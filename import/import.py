#!/usr/bin/env python3
"""
Optimized version of update_quotes.py.

Usage:
    Process all symbols:
        python update_quotes.py
    Process a specific symbol:
        python update_quotes.py AAPL
"""

import argparse
import sys
import datetime

import yfinance as yf
import mysql.connector
from dbconfig import DB_CONFIG


def process_symbol(symbol_key, my_ticker, cursor, conn, history_period="7d"):
    """Fetch historical quotes and insert new records for a given symbol."""
    print(f"Looking up '{symbol_key}' for my ticker '{my_ticker}'")
    
    try:
        ticker_obj = yf.Ticker(symbol_key)
        data = ticker_obj.history(period=history_period)
    except Exception as e:
        print(f"Error retrieving data for ticker {symbol_key}: {e}")
        return 0

    if data.empty:
        print(f"No historical data found for ticker {symbol_key}.")
        return 0

    # Update beta in the symbol table
    beta = ticker_obj.info.get("beta") or 1
    print(f"  Beta for {symbol_key} is {beta}")
    try:
        cursor.execute("UPDATE symbol SET beta = %s WHERE other_symbol = %s", (beta, symbol_key))
        conn.commit()
    except Exception as e:
        print(f"Error updating beta for symbol {symbol_key}: {e}")
        return 0

    # Pre-fetch existing quote dates for the symbol to avoid per-row checks.
    cursor.execute("SELECT quote_date FROM quotes WHERE symbol = %s", (my_ticker,))
    existing_dates = {row[0] for row in cursor.fetchall()}

    new_records = []
    for date, row in data.iterrows():
        quote_date = date.date()
        if quote_date in existing_dates:
            continue

        close_price = round(float(row.get("Close")), 3)
        volume = int(row.get("Volume"))
        dividends = float(row.get("Dividends", 0.0))
        # Use 1.00 if no split occurred.
        splits = float(row.get("Stock Splits", 0.0)) or 1.00

        new_records.append((my_ticker, quote_date, close_price, volume, dividends, splits))

    if new_records:
        insert_query = """
            INSERT INTO quotes (symbol, quote_date, close, volume, dividends, split)
            VALUES (%s, %s, %s, %s, %s, %s)
        """
        try:
            cursor.executemany(insert_query, new_records)
            conn.commit()
            for record in new_records:
                print(f"Inserted record for {record[0]} on {record[1]}, close = {record[2]}")
        except Exception as e:
            print(f"Error inserting records for {my_ticker}: {e}")
            return 0

    return len(new_records)


def lookup_without_update(ticker):
    """Lookup ticker without updating database and print the last close price."""
    print(f"Warning: '{ticker}' was not found in the symbol table. Attempting lookup…")
    try:
        data = yf.Ticker(ticker).history(period="7d")
    except Exception as e:
        print(f"Error retrieving data for ticker {ticker}: {e}")
        return

    if data.empty:
        print(f"No historical data found for ticker {ticker}.")
        return

    last_date = data.index[-1].date()
    last_close = data.iloc[-1]["Close"]
    print(f"Ticker {ticker} found. Last quote on {last_date}: Close = {last_close}")


def main():
    parser = argparse.ArgumentParser(
        description="Lookup quotes using yfinance and update the quotes table with new records."
    )
    parser.add_argument(
        "ticker",
        nargs="?",
        help="Ticker symbol. If found in the symbol table, update records; otherwise, display last close.",
    )
    args = parser.parse_args()

    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
    except Exception as e:
        print("Error connecting to the database:", e)
        sys.exit(1)

    try:
        cursor.execute("SELECT other_symbol, symbol FROM symbol WHERE active=1")
        symbol_dict = {row[0]: row[1] for row in cursor.fetchall()}
    except Exception as e:
        print("Error reading symbols from database:", e)
        cursor.close()
        conn.close()
        sys.exit(1)

    if args.ticker:
        input_ticker = args.ticker.strip().upper()
        # Find the key whose value matches the input (case-insensitive).
        found_key = next((key for key, value in symbol_dict.items() if value.upper() == input_ticker), None)
        if found_key:
            print(f"Ticker '{input_ticker}' found in symbol table as key '{found_key}'.")
            num_inserts = process_symbol(found_key, symbol_dict[found_key], cursor, conn, '1y')
            print(f"Inserted {num_inserts} new quote(s) for '{input_ticker}' (key: '{found_key}').")
        else:
            lookup_without_update(input_ticker)
    else:
        total_inserts = 0
        for key, ticker in symbol_dict.items():
            num_inserts = process_symbol(key, ticker, cursor, conn)
            print(f"Inserted {num_inserts} new quote(s) for symbol '{key}'.")
            total_inserts += num_inserts
        print(f"Total new quotes inserted: {total_inserts}")

    cursor.close()
    conn.close()


if __name__ == "__main__":
    main()
