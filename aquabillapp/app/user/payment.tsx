// app/(tabs)/payment.tsx

import Ionicons from '@expo/vector-icons/Ionicons';
import axios from 'axios';
import * as ImagePicker from 'expo-image-picker';
import { useRouter } from 'expo-router';
import React, { useRef, useState } from 'react';
import {
  Alert,
  Animated,
  Image,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { IP_ADDRESS } from '../utils/constants';

export default function PaymentScreen() {
  const router = useRouter();
  const [proofUri, setProofUri] = useState<string | null>(null);
  const [uploading, setUploading] = useState(false);
  const progress = useRef(new Animated.Value(0)).current;

  // profile overlay
  const [showProfile, setShowProfile] = useState(false);
  const fadeAnim = useRef(new Animated.Value(0)).current;

  const residentId  = 29;
  const collectorId = 1;
  const UPLOAD_URL  = `${IP_ADDRESS}/aquabill-api/upload_proof.php`;

  const pickProofImage = async () => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission required', 'Please grant access to your photos.');
      return;
    }
    const res = await ImagePicker.launchImageLibraryAsync({ quality: 0.7 });
    if (!res.canceled && res.assets.length) {
      setProofUri(res.assets[0].uri);
    }
  };

  const handleSubmit = async () => {
    if (!proofUri) {
      Alert.alert('No image', 'Please choose an image first.');
      return;
    }
    setUploading(true);
    progress.setValue(0);

    const formData = new FormData();
    formData.append('proof', {
      uri: proofUri,
      name: `proof_${Date.now()}.jpg`,
      type: 'image/jpeg',
    } as any);
    formData.append('resident_id', String(residentId));
    formData.append('collector_id', String(collectorId));

    try {
      await axios.post(UPLOAD_URL, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        timeout: 15000,
        onUploadProgress: evt => {
          const pct = evt.total ? evt.loaded / evt.total : 0;
          progress.setValue(pct);
        },
      });
      Alert.alert('✅ Success', 'Proof uploaded, collector notified!');
      setProofUri(null);
    } catch (e: any) {
      Alert.alert('⚠️ Error', e.message);
    } finally {
      // linger for 5 seconds so you can watch the bar
      setTimeout(() => {
        progress.setValue(0);
        setUploading(false);
      }, 5000);
    }
  };

  const barWidth = progress.interpolate({
    inputRange: [0, 1],
    outputRange: ['0%', '100%'],
  });

  const openProfile = () => {
    setShowProfile(true);
    Animated.timing(fadeAnim, { toValue: 1, duration: 300, useNativeDriver: true }).start();
  };
  const closeProfile = (path?: string) => {
    Animated.timing(fadeAnim, { toValue: 0, duration: 200, useNativeDriver: true }).start(() => {
      setShowProfile(false);
      if (path) router.push(path);
    });
  };

  return (
    <View style={styles.wrapper}>
      {uploading && (
        <View style={styles.progressOverlay}>
          <Text style={styles.progressLabel}>Uploading…</Text>
          <View style={styles.progressContainer}>
            <Animated.View style={[styles.progressFill, { width: barWidth }]} />
          </View>
        </View>
      )}

      <ScrollView contentContainerStyle={styles.screen}>
        <View style={styles.topBar}>
          <Text style={styles.topTitle}>Payment</Text>
          <TouchableOpacity onPress={openProfile}>
            <Ionicons name="person-circle" size={44} color="white" />
          </TouchableOpacity>
        </View>

        <View style={styles.content}>
          <Text style={styles.heading}>Upload Proof of Payment</Text>
          <View style={styles.card}>
            {!proofUri ? (
              <TouchableOpacity style={styles.primaryButton} onPress={pickProofImage}>
                <Ionicons name="cloud-upload-outline" size={20} color="#0077B6" />
                <Text style={styles.primaryText}>Choose Image</Text>
              </TouchableOpacity>
            ) : (
              <>
                <Image source={{ uri: proofUri }} style={styles.preview} />
                <TouchableOpacity
                  style={styles.secondaryButton}
                  onPress={pickProofImage}
                  disabled={uploading}
                >
                  <Ionicons name="refresh-outline" size={18} color="#0077B6" />
                  <Text style={styles.secondaryText}>Choose Again</Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={[styles.secondaryButton, styles.submitButton]}
                  onPress={handleSubmit}
                  disabled={uploading}
                >
                  <Ionicons name="send-outline" size={18} color="white" />
                  <Text style={[styles.secondaryText, styles.submitText]}>
                    Submit Proof
                  </Text>
                </TouchableOpacity>
              </>
            )}
          </View>
        </View>
      </ScrollView>

      {showProfile && (
        <Animated.View style={[styles.overlay, { opacity: fadeAnim }]}>
          <View style={styles.modal}>
            <TouchableOpacity style={styles.closeBtn} onPress={() => closeProfile()}>
              <Ionicons name="close" size={24} color="black" />
            </TouchableOpacity>
            <Ionicons name="person-circle-outline" size={100} color="#9ca3af" />
            <Text style={styles.profileName}>Profile Name</Text>
            <TouchableOpacity style={styles.modalBtn} onPress={() => closeProfile('/help')}>
              <Ionicons name="help-circle-outline" size={20} color="white" />
              <Text style={styles.modalTxt}>Help & Support</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.modalBtn} onPress={() => closeProfile('/settings')}>
              <Ionicons name="settings-outline" size={20} color="white" />
              <Text style={styles.modalTxt}>Settings</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.modalBtn} onPress={() => closeProfile('/')}>
              <Ionicons name="log-out-outline" size={20} color="white" />
              <Text style={styles.modalTxt}>Log Out</Text>
            </TouchableOpacity>
          </View>
        </Animated.View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: { flex: 1, position: 'relative' },
  progressOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(255,255,255,0.9)',
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  progressLabel: { fontSize: 18, marginBottom: 12, color: '#0077B6' },
  progressContainer: {
    width: '100%',
    height: 12,
    backgroundColor: '#eee',
    borderRadius: 6,
    overflow: 'hidden',
  },
  progressFill: { height: '100%', backgroundColor: '#00bfa5' },

  screen: { flexGrow: 1, backgroundColor: '#f9fafb' },
  topBar: {
    height: 90,
    backgroundColor: '#0077B6',
    paddingHorizontal: 20,
    paddingTop: 30,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  topTitle: { color: 'white', fontSize: 24, fontWeight: 'bold' },

  content: { flex: 1, padding: 20, alignItems: 'center' },
  heading: { fontSize: 20, fontWeight: '600', color: '#023E8A', marginBottom: 16 },

  card: {
    width: '100%',
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 16,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },

  primaryButton: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#0077B6',
    borderRadius: 8,
    paddingVertical: 12,
    paddingHorizontal: 24,
  },
  primaryText: {
    marginLeft: 8,
    fontSize: 16,
    fontWeight: '600',
    color: '#0077B6',
  },

  preview: {
    width: 250,
    height: 250,
    borderRadius: 12,
    marginBottom: 16,
    resizeMode: 'cover',
  },

  secondaryButton: {
    width: '80%',
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#0077B6',
    borderRadius: 8,
    paddingVertical: 10,
    justifyContent: 'center',
    marginBottom: 12,
  },
  secondaryText: {
    marginLeft: 8,
    fontSize: 16,
    fontWeight: '600',
    color: '#0077B6',
  },

  submitButton: { backgroundColor: '#0077B6' },
  submitText: { color: 'white' },

  overlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modal: {
    width: '80%',
    backgroundColor: 'white',
    padding: 20,
    borderRadius: 12,
    alignItems: 'center',
  },
  closeBtn: { alignSelf: 'flex-end' },
  profileName: { fontSize: 18, fontWeight: 'bold', marginVertical: 12 },

  modalBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#0077B6',
    padding: 12,
    borderRadius: 8,
    width: '100%',
    justifyContent: 'center',
    marginTop: 8,
  },
  modalTxt: { color: 'white', marginLeft: 8 },
});
