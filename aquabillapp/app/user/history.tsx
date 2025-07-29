// app/user/history.tsx

import Ionicons from '@expo/vector-icons/Ionicons';
import { useFocusEffect, useRouter } from 'expo-router';
import React, { useCallback, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Image,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { IP_ADDRESS } from '../utils/constants';

type HistoryItem = {
  id: string;
  description: string;
  date: string;
};

export default function History() {
  const router = useRouter();
  const [history, setHistory] = useState<HistoryItem[]>([]);
  const [loading, setLoading] = useState(true);
  const profilePicUrl = null; // or your real URL

  const fetchHistory = async () => {
    try {
      // Use the correct host for your environment
      const res  = await fetch(`${IP_ADDRESS}/aquabill-api/history.php`);
      const json = await res.json();
      if (json.status === 'success') {
        const mapped: HistoryItem[] = json.data.map((h: any) => ({
          id:          h.id,
          // Build a sentence describing the payment
          description: `You have paid ₱${parseFloat(h.amount).toFixed(2)}`,
          date:        h.date,
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

  const handleProfileMenu = () => {
    Alert.alert('User Menu', '', [
      { text: 'Cancel', style: 'cancel' },
      { text: 'Logout', onPress: () => router.replace('/') },
    ]);
  };

  const renderItem = ({ item }: { item: HistoryItem }) => {
    const formattedDate = new Date(item.date).toLocaleDateString('en-US', {
      year:  'numeric',
      month: 'long',
      day:   'numeric',
    });

    return (
      <View style={styles.entry}>
        <Text style={styles.entryText}>
          {item.description} on {formattedDate}
        </Text>
      </View>
    );
  };

  return (
    <View style={styles.screen}>
      {/* Top Bar */}
      <View style={styles.topBar}>
        <Text style={styles.title}>History</Text>
        <TouchableOpacity onPress={handleProfileMenu}>
          {profilePicUrl ? (
            <Image source={{ uri: profilePicUrl }} style={styles.avatar} />
          ) : (
            <Ionicons name="person-circle" size={44} color="white" />
          )}
        </TouchableOpacity>
      </View>

      {/* Body */}
      {loading ? (
        <ActivityIndicator style={{ marginTop: 40 }} size="large" color="#0077B6" />
      ) : (
        <FlatList
          data={history}
          keyExtractor={(item) => item.id}
          renderItem={renderItem}
          contentContainerStyle={styles.list}
          ListEmptyComponent={
            <Text style={styles.emptyText}>No history records yet.</Text>
          }
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  screen:  { flex: 1, backgroundColor: '#fff' },
  topBar:  {
    height:           90,
    backgroundColor:  '#0077B6',
    paddingHorizontal:20,
    paddingTop:       30,
    flexDirection:    'row',
    alignItems:       'center',
    justifyContent:   'space-between',
  },
  title:   { color: 'white', fontSize: 24, fontWeight: 'bold' },
  avatar:  { width: 44, height: 44, borderRadius: 22 },
  list:    { padding: 20 },
  entry:   {
    backgroundColor: '#e0f0ff',
    padding:         15,
    borderRadius:    10,
    marginBottom:    15,
  },
  entryText:{ fontSize: 16, color: '#333' },
  emptyText:{ textAlign: 'center', marginTop: 50, color: '#999' },
});
