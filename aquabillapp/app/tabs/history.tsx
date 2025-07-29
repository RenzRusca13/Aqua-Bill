// app/(tabs)/history.tsx

import { useFocusEffect } from 'expo-router';
import React, { useCallback, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  SafeAreaView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { IP_ADDRESS } from '../utils/constants';

type HistoryItem = {
  id: string;
  resident_name: string;
  amount: string;
  date: string;
};

export default function HistoryScreen() {
  const [history, setHistory] = useState<HistoryItem[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchHistory = async () => {
    try {
      const res = await fetch
      (`${IP_ADDRESS}/aquabill-api/history.php`);
      const json = await res.json();
      console.log('history.php →', json);

      if (json.status === 'success') {
        const mapped: HistoryItem[] = json.data.map((h: any) => ({
          id:            h.id,
          resident_name: h.resident_name,
          amount:        h.amount,
          date:          h.date,    // match the PHP alias
        }));
        setHistory(mapped);
      } else {
        throw new Error(json.message);
      }
    } catch (e) {
      console.warn('Failed to load history:', e);
      setHistory([]);
    } finally {
      setLoading(false);
    }
  };

  useFocusEffect(
    useCallback(() => {
      setLoading(true);
      fetchHistory();
    }, [])
  );

  const renderItem = ({ item }: { item: HistoryItem }) => {
    const formattedDate = new Date(item.date).toLocaleDateString('en-US', {
      year:  'numeric',
      month: 'long',
      day:   'numeric',
    });

    return (
      <View style={styles.entry}>
        <Text style={styles.entryText}>
          {item.resident_name} has paid ₱{parseFloat(item.amount).toFixed(2)} on {formattedDate}
        </Text>
      </View>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <Text style={styles.title}>Payment History</Text>
      {loading ? (
        <ActivityIndicator
          style={{ marginTop: 40 }}
          size="large"
          color="#0077B6"
        />
      ) : (
        <FlatList
          data={history}
          keyExtractor={(i) => i.id}
          renderItem={renderItem}
          contentContainerStyle={styles.list}
          ListEmptyComponent={
            <Text style={styles.emptyText}>No payment history found.</Text>
          }
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: 'white' },
  title: {
    fontSize:        24,
    fontWeight:      'bold',
    padding:         20,
    backgroundColor: '#0077B6',
    color:           'white',
  },
  list: { padding: 20 },
  entry: {
    padding:        12,
    marginBottom:   12,
    backgroundColor:'#f3f4f6',
    borderRadius:   8,
  },
  entryText: { fontSize: 16, color: '#333' },
  emptyText: {
    textAlign:  'center',
    marginTop:  40,
    fontSize:   16,
    color:      '#666',
  },
});
