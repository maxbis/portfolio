import sys
import yfinance as yf
from symbols import symbols

# Check if a command-line argument was provided.
if len(sys.argv) > 1:
    target_key = sys.argv[1]
    ticker = yf.Ticker(target_key)
    print(f"Fetching data for {target_key}")
    hist = ticker.history(period="1y")
    hist.index = hist.index.strftime("%Y-%m-%d")
    json_data = hist.to_json(orient='index')
    print(json_data)
    cleaned_key = target_key.replace("=", "")
    file_name = f"cache/{cleaned_key}.json"
    with open(file_name, 'w') as f:
        f.write(json_data)

    sys.exit(0)
    if target_key in symbols:
        # Only process the symbol specified by the user.
        keys_to_process = {target_key: symbols[target_key]}
    else:
        print(f"Symbol '{target_key}' not found in symbols. Available symbols are:")
        for key in symbols.keys():
            print(f"  {key}")
        sys.exit(1)
else:
    # No argument provided; process all symbols.
    keys_to_process = symbols

# Iterate over each symbol in the dictionary (or the single symbol if provided)
for key, value in keys_to_process.items():
    # Fetch the historical data for the symbol
    ticker = yf.Ticker(value)
    hist = ticker.history(period="1y")
    
    # Reformat the DataFrame index to have only the date (yyyy-mm-dd)
    hist.index = hist.index.strftime("%Y-%m-%d")
    
    # Convert the DataFrame to JSON with dates as keys
    json_data = hist.to_json(orient='index')
    
    # Save the JSON data to a file named <key>.json (remove any '=' characters from the key)
    cleaned_key = key.replace("=", "")
    file_name = f"cache/{cleaned_key}.json"
    with open(file_name, 'w') as f:
        f.write(json_data)
    
    # Always print the latest close price if data is available
    if not hist.empty:
        latest_close = hist['Close'].iloc[-1]
        print(f"Latest close price for {key}: {latest_close}")
    else:
        print(f"No data available for {key}")
    
    print(f"Data for {key} has been written to {file_name}")
