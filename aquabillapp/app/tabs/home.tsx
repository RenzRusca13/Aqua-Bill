// app/tabs/home.tsx

import { useFocusEffect, useRouter } from 'expo-router';
import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
  Alert,
  Animated,
  Image,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import Ionicons from 'react-native-vector-icons/Ionicons';
import { IP_ADDRESS } from '../utils/constants';

type Resident = {
  id: string;
  name: string;
  status: 'paid' | 'unpaid';
};

export default function HomeScreen() {
  const [residents, setResidents] = useState<Resident[]>([]);
  const [profilePicUrl, setProfilePicUrl] = useState<string | null>(null);
  const [showProfile, setShowProfile] = useState(false);
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const router = useRouter();

  const fetchResidents = async () => {
    try {
      const resp = await fetch(`${IP_ADDRESS}/aquabill-api/residents.php`);
      const json = await resp.json();
      if (json.status === 'success') setResidents(json.data);
      else Alert.alert('❌ Failed', 'Could not load residents.');
    } catch {
      Alert.alert('⚠️ Network Error', 'Cannot connect to server.');
    }
  };

  useFocusEffect(
    useCallback(() => {
      return () => {
        Animated.timing(fadeAnim, {
          toValue: 0,
          duration: 200,
          useNativeDriver: true,
        }).start(() => setShowProfile(false));
      };
    }, [])
  );

  useEffect(() => {
    fetchResidents();
    const iv = setInterval(fetchResidents, 5000);
    return () => clearInterval(iv);
  }, []);

  const total = residents.length;
  const paid = residents.filter(r => r.status === 'paid').length;
  const unpaid = total - paid;

  const openProfile = () => {
    setShowProfile(true);
    Animated.timing(fadeAnim, {
      toValue: 1,
      duration: 300,
      useNativeDriver: true,
    }).start();
  };
  const closeProfile = (path?: string) => {
    Animated.timing(fadeAnim, {
      toValue: 0,
      duration: 200,
      useNativeDriver: true,
    }).start(() => {
      setShowProfile(false);
      if (path) router.push(path);
    });
  };

  return (
    <View style={styles.container}>
      {/* Top Bar */}
      <View style={styles.topBar}>
        <Text style={styles.topTitle}>Dashboard</Text>
        <TouchableOpacity onPress={openProfile}>
          {profilePicUrl
            ? <Image source={{ uri: profilePicUrl }} style={styles.avatar} />
            : <Ionicons name="person-circle" size={44} color="white" />
          }
        </TouchableOpacity>
      </View>

      {/* Welcome */}
      <View style={styles.welcome}>
        <Text style={styles.welcomeTitle}>Welcome back 👋</Text>
        <Text style={styles.welcomeSub}>
          Here's a summary of your current resident billing status.
        </Text>
      </View>

      {/* Cards */}
      <View style={styles.cards}>
        <View style={styles.card}>
          <Ionicons name="people" size={28} color="#0077B6" />
          <View style={styles.cardText}>
            <Text style={styles.cardLabel}>Total Residents</Text>
            <Text style={styles.cardValue}>{total}</Text>
          </View>
        </View>
        <View style={styles.card}>
          <Ionicons name="checkmark-circle" size={28} color="#38b000" />
          <View style={styles.cardText}>
            <Text style={styles.cardLabel}>Paid</Text>
            <Text style={styles.cardValue}>{paid}</Text>
          </View>
        </View>
        <View style={styles.card}>
          <Ionicons name="close-circle" size={28} color="#ef4444" />
          <View style={styles.cardText}>
            <Text style={styles.cardLabel}>Unpaid</Text>
            <Text style={styles.cardValue}>{unpaid}</Text>
          </View>
        </View>
      </View>

      {/* Profile Modal */}
      {showProfile && (
        <Animated.View style={[styles.modalOverlay, { opacity: fadeAnim }]}>
          <View style={styles.modalContainer}>
            <TouchableOpacity style={styles.modalClose} onPress={() => closeProfile()}>
              <Ionicons name="close" size={24} color="black" />
            </TouchableOpacity>
            <View style={styles.modalPic}>
              <Ionicons name="person-circle-outline" size={100} color="#9ca3af" />
            </View>
            <Text style={styles.modalName}>Profile Name</Text>
            <TouchableOpacity
              style={styles.modalButton}
              onPress={() => closeProfile('/help')}
            >
              <Ionicons name="help-circle-outline" size={24} color="white" style={styles.modalIcon} />
              <Text style={styles.modalButtonText}>Help &amp; Support</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.modalButton}
              onPress={() => closeProfile('/settings')}
            >
              <Ionicons name="settings-outline" size={24} color="white" style={styles.modalIcon} />
              <Text style={styles.modalButtonText}>Settings</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.modalButton}
              onPress={() => closeProfile('/')}
            >
              <Ionicons name="log-out-outline" size={24} color="white" style={styles.modalIcon} />
              <Text style={styles.modalButtonText}>Log Out</Text>
            </TouchableOpacity>
          </View>
        </Animated.View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#fff' },
  topBar: {
    height: 90, backgroundColor: '#0077B6',
    paddingTop: 30, paddingHorizontal: 20,
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
  },
  topTitle: { color: 'white', fontSize: 24, fontWeight: 'bold' },
  avatar: { width: 44, height: 44, borderRadius: 22 },
  welcome: { padding: 20 },
  welcomeTitle: { fontSize: 18, fontWeight: '600', marginBottom: 10 },
  welcomeSub: { fontSize: 14, color: '#555' },
  cards: { paddingHorizontal: 20, gap: 16 },
  card: {
    backgroundColor: '#f1f5f9', padding: 16, borderRadius: 12,
    flexDirection: 'row', alignItems: 'center',
    shadowColor: '#000', shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.1, shadowRadius: 6, elevation: 4,
  },
  cardText: { marginLeft: 12 },
  cardLabel: { fontSize: 14, color: '#555' },
  cardValue: { fontSize: 20, fontWeight: 'bold' },

  modalOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center', alignItems: 'center',
  },
  modalContainer: {
    width: '80%', backgroundColor: 'white', padding: 20,
    borderRadius: 12, elevation: 10,
    shadowColor: '#000', shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.2, shadowRadius: 4, alignItems: 'center',
  },
  modalClose: { alignSelf: 'flex-end' },
  modalPic: {
    width: 150, height: 150, borderRadius: 75,
    backgroundColor: '#f3f4f6', justifyContent: 'center', alignItems: 'center',
    marginBottom: 10,
  },
  modalName: { fontSize: 18, fontWeight: 'bold', marginBottom: 20 },
  modalButton: {
    backgroundColor: '#0077B6', padding: 12,
    borderRadius: 8, flexDirection: 'row', alignItems: 'center',
    width: '100%', marginBottom: 10,
  },
  modalButtonText: { color: 'white', fontSize: 16, marginLeft: 10 },
  modalIcon: { marginRight: 8 },
});
