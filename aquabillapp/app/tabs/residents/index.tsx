// app/tabs/ResidentsScreen.tsx
import { IP_ADDRESS } from '@/app/utils/constants';
import { Picker } from '@react-native-picker/picker';
import { useFocusEffect, useRouter } from 'expo-router';
import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
  Alert,
  Animated,
  FlatList,
  Image,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import Ionicons from 'react-native-vector-icons/Ionicons';

type Resident = {
  id: string;
  name: string;
  status: 'paid' | 'unpaid';
};

export default function ResidentsScreen() {
  const [residents, setResidents] = useState<Resident[]>([]);
  const [filter, setFilter] = useState<'all' | 'paid' | 'unpaid'>('all');
  const [search, setSearch] = useState('');
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

  const filtered = residents.filter(r => {
    const okFilter = filter === 'all' || r.status === filter;
    const okSearch = r.name.toLowerCase().includes(search.toLowerCase());
    return okFilter && okSearch;
  });

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
        <Text style={styles.topTitle}>Households</Text>
        <TouchableOpacity onPress={openProfile}>
          {profilePicUrl ? (
            <Image source={{ uri: profilePicUrl }} style={styles.avatar} />
          ) : (
            <Ionicons name="person-circle" size={44} color="white" />
          )}
        </TouchableOpacity>
      </View>

      {/* Search & Filter */}
      <View style={styles.searchContainer}>
        <TextInput
          placeholder="Search resident..."
          value={search}
          onChangeText={setSearch}
          style={styles.searchInput}
        />
        <Text style={styles.filterLabel}>Filter by Status:</Text>
        <View style={styles.pickerWrapper}>
          <Picker
            selectedValue={filter}
            onValueChange={v => setFilter(v as any)}
            style={styles.picker}
          >
            <Picker.Item label={`All (${residents.length})`} value="all" />
            <Picker.Item
              label={`Paid (${residents.filter(r => r.status === 'paid').length})`}
              value="paid"
            />
            <Picker.Item
              label={`Unpaid (${residents.filter(r => r.status === 'unpaid').length})`}
              value="unpaid"
            />
          </Picker>
        </View>
      </View>

      {/* List */}
      <FlatList
        data={filtered}
        contentContainerStyle={styles.list}
        keyExtractor={i => i.id}
        renderItem={({ item }) => (
          <TouchableOpacity onPress={() => router.push(`/tabs/residents/${item.id}`)}>
            <View style={styles.card}>
              <View style={styles.avatarWrapper}>
                <Ionicons name="person" size={22} color="#9ca3af" />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={styles.name}>{item.name}</Text>
              </View>
              <Text
                style={[
                  styles.status,
                  { color: item.status === 'paid' ? '#38b000' : '#ef4444' },
                ]}
              >
                {item.status.toUpperCase()}
              </Text>
            </View>
          </TouchableOpacity>
        )}
      />

      {/* Profile Modal */}
      {showProfile && (
        <Animated.View style={[overlay.overlay, { opacity: fadeAnim }]}>
          <View style={overlay.container}>
            <TouchableOpacity style={overlay.closeBtn} onPress={() => closeProfile()}>
              <Ionicons name="close" size={24} color="black" />
            </TouchableOpacity>
            <View style={overlay.profilePic}>
              <Ionicons name="person-circle-outline" size={100} color="#9ca3af" />
            </View>
            <Text style={overlay.profileName}>Profile Name</Text>
            <TouchableOpacity
              style={overlay.button}
              onPress={() => closeProfile('/help')}
            >
              <Ionicons name="help-circle-outline" size={24} color="white" style={overlay.icon} />
              <Text style={overlay.buttonText}>Help & Support</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={overlay.button}
              onPress={() => closeProfile('/settings')}
            >
              <Ionicons name="settings-outline" size={24} color="white" style={overlay.icon} />
              <Text style={overlay.buttonText}>Settings</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={overlay.button}
              onPress={() => closeProfile('/')}
            >
              <Ionicons name="log-out-outline" size={24} color="white" style={overlay.icon} />
              <Text style={overlay.buttonText}>Log Out</Text>
            </TouchableOpacity>
          </View>
        </Animated.View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: 'white' },
  topBar: {
    height: 90,
    backgroundColor: '#0077B6',
    paddingTop: 30,
    paddingHorizontal: 20,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  topTitle: { color: 'white', fontSize: 24, fontWeight: 'bold' },
  avatar: { width: 44, height: 44, borderRadius: 22 },
  searchContainer: { paddingHorizontal: 20, marginTop: 10 },
  searchInput: {
    backgroundColor: '#F3F4F6',
    paddingHorizontal: 15,
    paddingVertical: 10,
    borderRadius: 10,
    marginBottom: 10,
    fontSize: 14,
  },
  filterLabel: { fontWeight: 'bold', marginBottom: 6 },
  pickerWrapper: {
    borderWidth: 1,
    borderColor: '#ccc',
    borderRadius: 10,
    height: 50,
    overflow: 'hidden',
    justifyContent: 'center',
  },
  picker: { fontSize: 16 },
  list: { padding: 20, paddingBottom: 120 },
  card: {
    backgroundColor: '#f1f5f9',
    marginBottom: 8,
    padding: 10,
    borderRadius: 8,
    flexDirection: 'row',
    alignItems: 'center',
  },
  avatarWrapper: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#e5e7eb',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 10,
  },
  name: { fontSize: 15, fontWeight: '600', color: '#111827' },
  status: { fontWeight: 'bold', fontSize: 13 },
});

const overlay = {
  overlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  container: {
    backgroundColor: 'white',
    width: '90%',
    borderRadius: 12,
    padding: 20,
    alignItems: 'center',
    elevation: 10,
    shadowColor: '#000',
    shadowOpacity: 0.2,
    shadowOffset: { width: 0, height: 3 },
    shadowRadius: 4,
  },
  closeBtn: { alignSelf: 'flex-end' },
  profilePic: {
    width: 150,
    height: 150,
    borderRadius: 75,
    backgroundColor: '#f3f4f6',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
  },
  profileName: { fontSize: 18, fontWeight: 'bold', marginBottom: 20 },
  button: {
    flexDirection: 'row',
    backgroundColor: '#0077B6',
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
    width: '100%',
    marginBottom: 10,
  },
  icon: { marginRight: 8 },
  buttonText: { color: 'white', fontSize: 16 },
};
