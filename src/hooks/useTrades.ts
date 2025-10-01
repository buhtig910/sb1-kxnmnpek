import { useEffect, useState } from 'react';
import { supabase } from '../lib/supabase';
import { useAuth } from '../contexts/AuthContext';
import type { Database } from '../lib/database.types';

type Trade = Database['public']['Tables']['trades']['Row'];

export function useTrades() {
  const { user } = useAuth();
  const [trades, setTrades] = useState<Trade[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchTrades = async () => {
    if (!user) {
      setTrades([]);
      setLoading(false);
      return;
    }

    const { data, error } = await supabase
      .from('trades')
      .select('*')
      .eq('user_id', user.id)
      .order('created_at', { ascending: false })
      .limit(50);

    if (!error && data) {
      setTrades(data);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchTrades();
  }, [user?.id]);

  return { trades, loading, refetch: fetchTrades };
}
