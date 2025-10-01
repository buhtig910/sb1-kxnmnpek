import { useEffect, useState } from 'react';
import { supabase } from '../lib/supabase';
import { useAuth } from '../contexts/AuthContext';
import type { Database } from '../lib/database.types';

type Position = Database['public']['Tables']['positions']['Row'];

export function usePositions() {
  const { user } = useAuth();
  const [positions, setPositions] = useState<Position[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchPositions = async () => {
    if (!user) {
      setPositions([]);
      setLoading(false);
      return;
    }

    const { data, error } = await supabase
      .from('positions')
      .select('*')
      .eq('user_id', user.id)
      .order('updated_at', { ascending: false });

    if (!error && data) {
      setPositions(data);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchPositions();
  }, [user?.id]);

  return { positions, loading, refetch: fetchPositions };
}
