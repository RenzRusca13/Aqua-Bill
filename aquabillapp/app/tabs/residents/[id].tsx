// app/(tabs)/residents/[id].tsx

import { IP_ADDRESS } from '@/app/utils/constants';
import { router, useFocusEffect, useLocalSearchParams } from 'expo-router';
import React, { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Animated,
  Modal,
  SafeAreaView,
  ScrollView,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import QRCode from 'react-native-qrcode-svg';
import Ionicons from 'react-native-vector-icons/Ionicons';

type Resident = {
  id: string;
  name: string;
  status: string;
  age: string;
  gender: string;
  email: string;
  contact: string;
  meter_no: string;
  payment_mode?: string;
};

export default function ResidentDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const [resident, setResident] = useState<Resident | null>(null);
  const [loading, setLoading] = useState(false);
  const [showModal, setShowModal] = useState(false);
  const [showUndo, setShowUndo] = useState(false);

  // Animated value for overlay fade and panel slide
  const overlayOpacity = useState(() => new Animated.Value(0))[0];

  // Fetch resident data
  const fetchResident = async () => {
    try {
      const cleanId = encodeURIComponent(String(id).trim());
      const res = await fetch(
        `${IP_ADDRESS}/aquabill-api/residents.php?id=${cleanId}`
      );
      const json = await res.json();
      if (json.status === 'success') setResident(json.data);
      else Alert.alert('Error', json.message);
    } catch {
      Alert.alert('Network Error', 'Could not connect to the server.');
    }
  };

  // Load on focus
  useFocusEffect(
    useCallback(() => {
      async function load() {
        await fetchResident();
      }
      load();
    }, [id])
  );

  // Animate overlay opacity
  useEffect(() => {
    const toValue = showModal || showUndo ? 0.4 : 0;
    Animated.timing(overlayOpacity, {
      toValue,
      duration: 200,
      useNativeDriver: true,
    }).start();
  }, [showModal, showUndo, overlayOpacity]);

  // Color helper
  const getStatusColor = (s: string) => (s === 'paid' ? '#38b000' : '#ef4444');

  // Confirm payment
  const confirmPayment = async (mode: 'Cash' | 'G-Cash') => {
    if (!resident) return;
    setLoading(true);
    try {
      const res = await fetch(
        `${IP_ADDRESS}/aquabill-api/residents.php`,
        {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: resident.id, payment_mode: mode }),
        }
      );
      const json = await res.json();
      if (json.status === 'success') {
        setShowModal(false);
        fetchResident();
      } else {
        Alert.alert('Error', json.message);
      }
    } catch {
      Alert.alert('Network Error', 'Could not connect to the server.');
    } finally {
      setLoading(false);
    }
  };

  // Undo payment
  const undoPayment = async () => {
    if (!resident) return;
    setLoading(true);
    try {
      const res = await fetch(
        `${IP_ADDRESS}/aquabill-api/residents.php`,
        {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: resident.id, undo: true }),
        }
      );
      const json = await res.json();
      if (json.status === 'success') {
        setShowUndo(false);
        fetchResident();
      } else {
        Alert.alert('Error', json.message);
      }
    } catch {
      Alert.alert('Network Error', 'Could not connect to the server.');
    } finally {
      setLoading(false);
    }
  };

  // Navigation helpers
  const handleAddBill = () => {
    if (resident?.status !== 'paid') {
      router.push(`/tabs/residents/add-bill?id=${resident.id}`);
    }
  };
  const handleOpenReceipt = async () => {
    try {
      const res = await fetch(
        `${IP_ADDRESS}/aquabill-api/get-latest-bill.php?resident_id=${resident?.id}`
      );
      const json = await res.json();
      if (json.status === 'success') {
        router.push(`/tabs/residents/receipt?id=${json.data.id}`);
      } else {
        Alert.alert('No Receipt', 'No bills found for this resident.');
      }
    } catch {
      Alert.alert('Error', 'Server error while loading receipt.');
    }
  };

  if (!resident) return null;

  // Slide interpolation
  const slideTranslate = overlayOpacity.interpolate({
    inputRange: [0, 0.4],
    outputRange: [300, 0],
  });

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: 'white' }}>
      {/* Header */}
      <View
        style={{
          height: 60,
          backgroundColor: '#0077B6',
          flexDirection: 'row',
          alignItems: 'center',
          paddingHorizontal: 16,
          borderBottomWidth: 1,
          borderBottomColor: '#ccc',
        }}
      >
        <TouchableOpacity
          style={{
            width: 36,
            height: 36,
            backgroundColor: 'white',
            borderRadius: 6,
            justifyContent: 'center',
            alignItems: 'center',
          }}
          onPress={() => router.back()}
        >
          <Ionicons name="arrow-back" size={22} color="#0077B6" />
        </TouchableOpacity>
      </View>

      {/* Scrollable Content */}
      <ScrollView contentContainerStyle={{ paddingBottom: 100 }}>
        <View style={{ flex: 1, alignItems: 'center', paddingTop: 40 }}>
          {/* Avatar */}
          <View
            style={{
              width: 160,
              height: 160,
              borderRadius: 80,
              backgroundColor: '#e0e0e0',
              justifyContent: 'center',
              alignItems: 'center',
              marginBottom: 20,
            }}
          >
            <Ionicons name="person" size={120} color="#9ca3af" />
          </View>

          {/* Name */}
          <Text
            style={{
              fontSize: 30,
              fontWeight: 'bold',
              color: '#333',
              marginBottom: 30,
            }}
          >
            {resident.name}
          </Text>

          {/* Details */}
          <View style={{ width: '85%' }}>
            {[
              ['Age', resident.age],
              ['Gender', resident.gender],
              ['Email', resident.email],
              ['Contact', resident.contact],
              ['Meter No.', resident.meter_no],
              ['Status', resident.status === 'paid' ? 'Paid' : 'Unpaid'],
              ...(resident.payment_mode
                ? [['Paid Via', resident.payment_mode]] as [string, string][]
                : []),
            ].map(([label, value]) => (
              <View key={label} style={{ flexDirection: 'row', marginBottom: 12 }}>
                <Text style={{ fontSize: 18, fontWeight: 'bold', width: 110 }}>
                  {label}:
                </Text>
                <Text
                  style={{
                    fontSize: 18,
                    color:
                      label === 'Status'
                        ? getStatusColor(resident.status)
                        : '#000',
                  }}
                >
                  {value}
                </Text>
              </View>
            ))}

            {/* QR Code for this profile */}
            <View style={{ alignItems: 'center', marginTop: 20 }}>
              <QRCode
                value={`/tabs/residents/${resident.id}`}
                size={150}
                logoBackgroundColor="transparent"
              />
              <Text style={{ marginTop: 8, fontSize: 14, color: '#555' }}>
                Scan to open this profile
              </Text>
            </View>
          </View>

          {/* Actions */}
          <View
            style={{
              flexDirection: 'row',
              justifyContent: 'space-between',
              width: '85%',
              marginTop: 30,
            }}
          >
            {/* Add Bill */}
            <View style={{ alignItems: 'center' }}>
              <TouchableOpacity
                style={{
                  backgroundColor:
                    resident.status === 'paid' ? '#fecaca' : '#0077B6',
                  borderRadius: 40,
                  width: 60,
                  height: 60,
                  justifyContent: 'center',
                  alignItems: 'center',
                  marginBottom: 6,
                }}
                onPress={handleAddBill}
                disabled={resident.status === 'paid'}
              >
                <Ionicons
                  name="add-circle-outline"
                  size={28}
                  color={resident.status === 'paid' ? '#991b1b' : 'white'}
                />
              </TouchableOpacity>
              <Text style={{ fontSize: 12, color: '#333' }}>
                {resident.status === 'paid' ? 'Disabled' : 'Add Bill'}
              </Text>
            </View>

            {/* Receipt */}
            <View style={{ alignItems: 'center' }}>
              <TouchableOpacity
                style={{
                  backgroundColor: '#0077B6',
                  borderRadius: 40,
                  width: 60,
                  height: 60,
                  justifyContent: 'center',
                  alignItems: 'center',
                  marginBottom: 6,
                }}
                onPress={handleOpenReceipt}
              >
                <Ionicons name="print-outline" size={28} color="white" />
              </TouchableOpacity>
              <Text style={{ fontSize: 12, color: '#333' }}>Receipt</Text>
            </View>

            {/* SMS */}
            <View style={{ alignItems: 'center' }}>
              <TouchableOpacity
                style={{
                  backgroundColor: '#0077B6',
                  borderRadius: 40,
                  width: 60,
                  height: 60,
                  justifyContent: 'center',
                  alignItems: 'center',
                  marginBottom: 6,
                }}
                onPress={() => {}}
              >
                <Ionicons
                  name="chatbubble-ellipses-outline"
                  size={28}
                  color="white"
                />
              </TouchableOpacity>
              <Text style={{ fontSize: 12, color: '#333' }}>SMS</Text>
            </View>

            {/* Verify / Undo */}
            <View style={{ alignItems: 'center' }}>
              <TouchableOpacity
                style={{
                  backgroundColor:
                    resident.status === 'paid' ? '#38b000' : '#0077B6',
                  borderRadius: 40,
                  width: 60,
                  height: 60,
                  justifyContent: 'center',
                  alignItems: 'center',
                  marginBottom: 6,
                  opacity: loading ? 0.6 : 1,
                }}
                onPress={() => {
                  if (resident.status === 'paid') setShowUndo(true);
                  else setShowModal(true);
                }}
                disabled={loading}
              >
                {loading ? (
                  <ActivityIndicator color="white" />
                ) : (
                  <Ionicons
                    name="checkmark-done-outline"
                    size={28}
                    color="white"
                  />
                )}
              </TouchableOpacity>
              <Text style={{ fontSize: 12, color: '#333' }}>
                {resident.status === 'paid' ? 'Verified' : 'Verify'}
              </Text>
            </View>
          </View>
        </View>
      </ScrollView>

      {/* Shared Fade Overlay */}
      <Animated.View
        pointerEvents={showModal || showUndo ? 'auto' : 'none'}
        style={{
          position: 'absolute',
          top: 0, left: 0, right: 0, bottom: 0,
          backgroundColor: 'black',
          opacity: overlayOpacity,
        }}
      />

      {/* Payment Mode Modal */}
      <Modal visible={showModal} transparent animationType="none">
        <Animated.View
          style={{
            position: 'absolute',
            left: 0, right: 0, bottom: 0,
            transform: [{ translateY: slideTranslate }],
          }}
        >
          <View
            style={{
              backgroundColor: 'white',
              borderTopLeftRadius: 12,
              borderTopRightRadius: 12,
              padding: 20,
            }}
          >
            <Text
              style={{
                fontSize: 20,
                marginBottom: 20,
                textAlign: 'center',
              }}
            >
              Mode of Payment
            </Text>
            <View
              style={{
                flexDirection: 'row',
                justifyContent: 'space-around',
                width: '100%',
              }}
            >
              {[
                { mode: 'Cash' as const, icon: 'cash-outline' },
                { mode: 'G-Cash' as const, icon: 'card-outline' },
              ].map(({ mode, icon }) => (
                <TouchableOpacity
                  key={mode}
                  style={{ alignItems: 'center', opacity: loading ? 0.5 : 1 }}
                  onPress={() => confirmPayment(mode)}
                  disabled={loading}
                >
                  <View
                    style={{
                      width: 60,
                      height: 60,
                      borderRadius: 30,
                      backgroundColor: '#e0e0e0',
                      justifyContent: 'center',
                      alignItems: 'center',
                      marginBottom: 8,
                    }}
                  >
                    <Ionicons name={icon} size={30} color="#0077B6" />
                  </View>
                  <Text>{mode}</Text>
                </TouchableOpacity>
              ))}
            </View>
            <TouchableOpacity
              style={{ marginTop: 20, alignSelf: 'center' }}
              onPress={() => setShowModal(false)}
            >
              <Text style={{ color: '#0077B6' }}>Cancel</Text>
            </TouchableOpacity>
          </View>
        </Animated.View>
      </Modal>

      {/* Undo Modal */}
      <Modal visible={showUndo} transparent animationType="none">
        <Animated.View
          style={{
            position: 'absolute',
            left: 0, right: 0, bottom: 0,
            transform: [{ translateY: slideTranslate }],
          }}
        >
          <View
            style={{
              backgroundColor: 'white',
              borderTopLeftRadius: 12,
              borderTopRightRadius: 12,
              padding: 20,
              alignItems: 'center',
            }}
          >
            <Text style={{ fontSize: 18, marginBottom: 20 }}>
              Do you wish to undo?
            </Text>
            <View
              style={{
                flexDirection: 'row',
                justifyContent: 'space-between',
                width: '100%',
              }}
            >
              <TouchableOpacity
                style={{
                  flex: 1,
                  padding: 12,
                  marginRight: 8,
                  backgroundColor: '#eee',
                  borderRadius: 6,
                  alignItems: 'center',
                }}
                onPress={() => setShowUndo(false)}
              >
                <Text>No</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={{
                  flex: 1,
                  padding: 12,
                  marginLeft: 8,
                  backgroundColor: '#ef4444',
                  borderRadius: 6,
                  alignItems: 'center',
                }}
                onPress={undoPayment}
                disabled={loading}
              >
                {loading ? (
                  <ActivityIndicator color="white" />
                ) : (
                  <Text style={{ color: 'white' }}>Yes</Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </Animated.View>
      </Modal>
    </SafeAreaView>
  );
}
