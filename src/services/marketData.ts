interface StockQuote {
  symbol: string;
  price: number;
  change: number;
  changePercent: number;
  high: number;
  low: number;
  volume: number;
}

const MOCK_STOCKS = [
  'AAPL', 'GOOGL', 'MSFT', 'AMZN', 'TSLA', 'META', 'NVDA', 'JPM', 'V', 'WMT'
];

const priceCache = new Map<string, { price: number; timestamp: number }>();

function generatePrice(symbol: string): number {
  const cached = priceCache.get(symbol);
  const now = Date.now();

  if (cached && now - cached.timestamp < 2000) {
    const volatility = 0.002;
    const change = (Math.random() - 0.5) * cached.price * volatility;
    const newPrice = Math.max(1, cached.price + change);
    priceCache.set(symbol, { price: newPrice, timestamp: now });
    return newPrice;
  }

  const basePrices: Record<string, number> = {
    'AAPL': 178.50,
    'GOOGL': 142.30,
    'MSFT': 378.90,
    'AMZN': 145.20,
    'TSLA': 242.80,
    'META': 485.60,
    'NVDA': 495.30,
    'JPM': 156.70,
    'V': 268.40,
    'WMT': 165.90,
  };

  const basePrice = basePrices[symbol] || 100;
  const variance = (Math.random() - 0.5) * basePrice * 0.1;
  const price = Math.max(1, basePrice + variance);

  priceCache.set(symbol, { price, timestamp: now });
  return price;
}

export async function getStockQuote(symbol: string): Promise<StockQuote> {
  await new Promise(resolve => setTimeout(resolve, 100));

  const price = generatePrice(symbol);
  const previousPrice = price * (1 + (Math.random() - 0.5) * 0.05);
  const change = price - previousPrice;
  const changePercent = (change / previousPrice) * 100;

  return {
    symbol,
    price: Number(price.toFixed(2)),
    change: Number(change.toFixed(2)),
    changePercent: Number(changePercent.toFixed(2)),
    high: Number((price * 1.02).toFixed(2)),
    low: Number((price * 0.98).toFixed(2)),
    volume: Math.floor(Math.random() * 10000000) + 1000000,
  };
}

export async function searchStocks(query: string): Promise<string[]> {
  await new Promise(resolve => setTimeout(resolve, 100));

  if (!query) return MOCK_STOCKS.slice(0, 5);

  return MOCK_STOCKS.filter(symbol =>
    symbol.toLowerCase().includes(query.toLowerCase())
  );
}

export function subscribeToPrice(
  symbol: string,
  callback: (quote: StockQuote) => void
): () => void {
  const interval = setInterval(async () => {
    const quote = await getStockQuote(symbol);
    callback(quote);
  }, 3000);

  getStockQuote(symbol).then(callback);

  return () => clearInterval(interval);
}
