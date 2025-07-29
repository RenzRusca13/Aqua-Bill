// app/user/userhome.tsx
import Ionicons from '@expo/vector-icons/Ionicons';
import { useRouter } from 'expo-router';
import { Alert, Image, Text, TouchableOpacity, View } from 'react-native';

export default function UserHome() {
  const router = useRouter();
  const profilePicUrl = null;

  const handleProfileMenu = () => {
    Alert.alert('User Menu', '', [
      { text: 'Cancel', style: 'cancel' },
      { text: 'Logout', onPress: () => router.replace('/') },
    ]);
  };

  return (
    <View style={{ flex: 1, backgroundColor: '#fff' }}>
      {/* 🔷 Top Bar */}
      <View
        style={{
          height: 90,
          backgroundColor: '#0077B6',
          paddingHorizontal: 20,
          paddingTop: 30,
          flexDirection: 'row',
          alignItems: 'center',
          justifyContent: 'space-between',
        }}
      >
        <Text style={{ color: 'white', fontSize: 24, fontWeight: 'bold' }}>
          Dashboard
        </Text>
        <TouchableOpacity onPress={handleProfileMenu}>
          {profilePicUrl ? (
            <Image
              source={{ uri: profilePicUrl }}
              style={{ width: 44, height: 44, borderRadius: 22 }}
            />
          ) : (
            <Ionicons name="person-circle" size={44} color="white" />
          )}
        </TouchableOpacity>
      </View>

      {/* Page Content */}
      <View style={{ padding: 20 }}>
        <Text>Welcome to the user home screen!</Text>
      </View>
    </View>
  );
}
