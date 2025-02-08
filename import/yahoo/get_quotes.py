import yfinance as yf
from symbols import symbols

# Iterate over each symbol in the dictionary
for key, value in symbols.items():
    # Fetch the historical data for the symbol
    ticker = yf.Ticker(value)
    hist = ticker.history(period="1y")

    # Reformat the DataFrame index to have only the date (yyyy-mm-dd)
    hist.index = hist.index.strftime("%Y-%m-%d")

    # Convert the DataFrame to JSON with dates as keys
    json_data = hist.to_json(orient='index')

    # Save the JSON data to a file named <key>.json
    key = key.replace("=", "")
    file_name = f"cache/{key}.json"
    with open(file_name, 'w') as f:
        f.write(json_data)

    print(f"Data for {key} has been written to {file_name}")