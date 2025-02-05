import yfinance as yf

# List of symbols you want to fetch data for
#symbols = ["AAPL", "MSFT", "GOOG", "NVDA", "META", "MSFT", "HIMS", "ASRNL.AS", "AMD", "QCOM", "TSM", "MU", "ABN.AS", "TSM", "ASML"]  # Add or remove symbols as needed
#symbols = ["ADYEY", "ARCAD.AS", "BFIT.AS",  "BSI.DE", "DTE.DE", "EVO.ST", "HEIA.AS", "ING", "JDEP.AS", "TKWY.AS", "NN.AS", "ONTEX.BR"]
#symbols = ["IAEX.AS", "IUSAA.XD", "IS3QD.XD", "IEVD.DE", "EXSA.DE", "STKX.MI", "SPYW.DE", "SPYD.DE", "VNRT.MU"]
symbols = ['EURUSD=X'];

for symbol in symbols:
    # Fetch the historical data for the symbol
    ticker = yf.Ticker(symbol)
    hist = ticker.history(period="1y")

    # Reformat the DataFrame index to have only the date (yyyy-mm-dd)
    hist.index = hist.index.strftime("%Y-%m-%d")

    # Convert the DataFrame to JSON with dates as keys
    json_data = hist.to_json(orient='index')

    # Save the JSON data to a file named <symbol>.json
    symbol = symbol.replace("=", "")
    file_name = f"cache/{symbol}.json"
    with open(file_name, 'w') as f:
        f.write(json_data)

    print(f"Data for {symbol} has been written to {file_name}")
