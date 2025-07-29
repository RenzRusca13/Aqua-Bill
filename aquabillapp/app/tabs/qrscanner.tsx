// app/tabs/qrscanner.tsx

import { CameraView, useCameraPermissions } from 'expo-camera';
import * as Haptics from 'expo-haptics';
import { useFocusEffect, useRouter } from 'expo-router';
import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
  Alert,
  Animated,
  Dimensions,
  StyleSheet,
  Text,
  TouchableOpacity,
  View
} from 'react-native';
import Ionicons from 'react-native-vector-icons/Ionicons';

const { width } = Dimensions.get('window');
const SCAN_BOX_SIZE = width * 0.7;

export default function QRScannerScreen() {
  const router = useRouter();
  const [scanned, setScanned] = useState(false);
  const [flash, setFlash] = useState<'off' | 'torch'>('off');
  const [permission, requestPermission] = useCameraPermissions();
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const scanLineAnim = useRef(new Animated.Value(0)).current;

  useFocusEffect(
    useCallback(() => {
      // reset fade when leaving
      return () => {
        Animated.timing(fadeAnim, { toValue: 0, duration: 200, useNativeDriver: true })
          .start();
      };
    }, [])
  );

  useEffect(() => {
    if (!scanned) {
      Animated.loop(
        Animated.sequence([
          Animated.timing(scanLineAnim, { toValue: 1, duration: 1500, useNativeDriver: true }),
          Animated.timing(scanLineAnim, { toValue: 0, duration: 1500, useNativeDriver: true }),
        ])
      ).start();
    } else {
      scanLineAnim.setValue(0);
    }
  }, [scanned]);

  const handleBarCodeScanned = ({ type, data }: { type: string; data: string }) => {
    setScanned(true);
    Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
    if (data) {
      router.push(data);
    } else {
      // fallback
      Alert.alert('QR Scanned', `Type: ${type}\nData: ${data}`);
    }

  
  };

  if (!permission) {
    return (
      <View style={styles.centered}>
        <Text>Checking camera permission...</Text>
      </View>
    );
  }
  if (!permission.granted) {
    return (
      <View style={styles.centered}>
        <Text style={{ marginBottom: 20 }}>Camera permission required</Text>
        <TouchableOpacity onPress={requestPermission} style={styles.button}>
          <Text style={styles.buttonText}>Grant Permission</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const scanLineY = scanLineAnim.interpolate({
    inputRange: [0, 1],
    outputRange: [0, SCAN_BOX_SIZE - 2],
  });

  return (
    <View style={styles.container}>
      <CameraView
        cameraType="back"
        flash={flash}
        onBarcodeScanned={scanned ? undefined : handleBarCodeScanned}
        barcodeScannerSettings={{ barcodeTypes: ['qr'] }}
        style={StyleSheet.absoluteFillObject}
      />

      {/* Top Bar */}
      <View style={styles.topBar}>
        <Text style={styles.topTitle}>QR Scanner</Text>
        <TouchableOpacity onPress={() => setScanned(false)}>
          <Ionicons name="refresh" size={28} color="white" />
        </TouchableOpacity>
      </View>

      {/* Scan Box */}
      <View style={styles.centerOverlay}>
        <View style={styles.scanBox}>
          {!scanned && (
            <Animated.View
              style={[
                styles.scanLine,
                { transform: [{ translateY: scanLineY }] },
              ]}
            />
          )}
        </View>
        <TouchableOpacity
          style={styles.flashButton}
          onPress={() => setFlash(f => (f === 'off' ? 'torch' : 'off'))}
        >
          <Ionicons
            name={flash === 'off' ? 'flashlight-outline' : 'flashlight'}
            size={32}
            color="white"
          />
        </TouchableOpacity>
      </View>

      {/* Rescan Button */}
      {scanned && (
        <TouchableOpacity
          style={styles.rescanButton}
          onPress={() => setScanned(false)}
        >
          <Text style={styles.rescanText}>Scan Again</Text>
        </TouchableOpacity>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  button: { backgroundColor: '#0077B6', padding: 12, borderRadius: 8 },
  buttonText: { color: '#fff', fontWeight: 'bold' },

  topBar: {
    position: 'absolute',
    top: 0, left: 0, right: 0,
    height: 90,
    paddingTop: 30,
    paddingHorizontal: 20,
    backgroundColor: '#0077B6',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    zIndex: 10,
  },
  topTitle: { color: 'white', fontSize: 24, fontWeight: 'bold' },

  centerOverlay: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  scanBox: {
    width: SCAN_BOX_SIZE,
    height: SCAN_BOX_SIZE,
    borderColor: 'white',
    borderWidth: 3,
    borderRadius: 12,
    backgroundColor: 'rgba(255,255,255,0.05)',
    overflow: 'hidden',
    justifyContent: 'center',
  },
  scanLine: { height: 2, backgroundColor: '#00FFDD', width: '100%', position: 'absolute' },
  flashButton: { marginTop: 30, backgroundColor: 'rgba(0,0,0,0.5)', padding: 14, borderRadius: 50 },

  rescanButton: {
    position: 'absolute',
    bottom: 80,
    alignSelf: 'center',
    backgroundColor: '#0077B6',
    paddingVertical: 12,
    paddingHorizontal: 28,
    borderRadius: 10,
  },
  rescanText: { color: 'white', fontWeight: 'bold', fontSize: 16 },
});
