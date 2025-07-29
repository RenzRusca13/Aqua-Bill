// app/(tabs)/residents/receipt.tsx

import { IP_ADDRESS } from '@/app/utils/constants';
import * as MediaLibrary from 'expo-media-library';
import { router, useLocalSearchParams } from 'expo-router';
import React, { useEffect, useRef, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Image,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import QRCode from 'react-native-qrcode-svg';
import Ionicons from 'react-native-vector-icons/Ionicons';
import { captureRef } from 'react-native-view-shot';

export default function ReceiptScreen() {
  const params = useLocalSearchParams<{ id?: string }>();
  let rawId = params.id;
  if (Array.isArray(rawId)) rawId = rawId[0];
  if (!rawId) {
    Alert.alert('Error', 'No bill ID provided.');
    router.back();
    return null;
  }
  const billId = rawId;

  const [bill, setBill] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const receiptRef = useRef<View>(null);

  useEffect(() => {
    (async () => {
      try {
        const res = await fetch(`${IP_ADDRESS}/aquabill-api/get-bill.php?id=${billId}`);
        const json = await res.json();
        if (json.status === 'success') setBill(json.data);
        else Alert.alert('Error', json.message);
      } catch {
        Alert.alert('Error', 'Server error.');
      } finally {
        setLoading(false);
      }
    })();
  }, [billId]);

  const handleDownload = async () => {
    const { status } = await MediaLibrary.requestPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission denied', 'You must allow gallery access to save receipts.');
      return;
    }

    try {
      const uri = await captureRef(receiptRef, { format: 'png', quality: 1 });
      const asset = await MediaLibrary.createAssetAsync(uri);
      await MediaLibrary.createAlbumAsync('AQUla Receipts', asset, false);
      Alert.alert('Saved!', 'Receipt has been saved to your gallery.');
    } catch {
      // silent
    }
  };

  if (loading) return <ActivityIndicator style={{ marginTop: 40 }} />;
  if (!bill) return <Text style={{ margin: 20 }}>Failed to load receipt.</Text>;

  const {
    id,
    resident_id,
    resident_name,
    meter_no,
    coverage_from,
    coverage_to,
    reading_date,
    due_date,
    consumption,
    price_per_cubic,
    total,
  } = bill;

  const fmt = (s: string) =>
    new Date(s).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });

  const from = fmt(coverage_from);
  const to = fmt(coverage_to);
  const read = fmt(reading_date);
  const due = fmt(due_date);
  const monthName = new Date(coverage_to).toLocaleString('default', { month: 'long' }).toUpperCase();
  const year = new Date(coverage_to).getFullYear();

  return (
    <View style={{ flex: 1, backgroundColor: 'white' }}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={22} color="#0077B6" />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent}>
        <View ref={receiptRef} collapsable={false} style={styles.container}>
          <Image source={require('../../../assets/images/logo.png')} style={styles.logo} />

          <Text style={styles.title}>BILLING NOTICE</Text>
          <Text style={styles.subTitle}>
            FOR THE MONTH OF {monthName} {year}
          </Text>

          <View style={styles.sep} />
          <Text>Reading Date: {read}</Text>
          <Text>Bill No: {id}</Text>
          <Text>
            From: {from} to {to}
          </Text>
          <View style={styles.sep} />

          <Text>{resident_name}</Text>
          <Text>Resident ID: {resident_id}</Text>
          <Text>Meter No: {meter_no || 'N/A'}</Text>
          <View style={styles.sep} />

          <View style={styles.row}>
            <Text>Consumption:</Text>
            <Text>₱ {parseFloat(consumption).toFixed(2)}</Text>
          </View>
          <View style={styles.row}>
            <Text>Price Per m³:</Text>
            <Text>₱ {parseFloat(price_per_cubic).toFixed(2)}</Text>
          </View>
          <View style={styles.row}>
            <Text>Current Bill:</Text>
            <Text>₱ {parseFloat(total).toFixed(2)}</Text>
          </View>
          <View style={styles.sep} />

          <View style={styles.row}>
            <Text style={{ fontWeight: 'bold' }}>TOTAL:</Text>
            <Text style={{ fontWeight: 'bold' }}>₱ {parseFloat(total).toFixed(2)}</Text>
          </View>
          <View style={styles.row}>
            <Text>DUE DATE:</Text>
            <Text>{due}</Text>
          </View>

          {/* Separator line above QR code */}
          <View style={styles.sep} />

          {/* QR Code linking to resident profile */}
          <View style={{ alignItems: 'center', marginTop: 10 }}>
            <QRCode
              value={`/tabs/residents/${resident_id}`}
              size={120}
              logoBackgroundColor="transparent"
            />
          </View>

          <Text style={styles.notice}>
            * Mangyaring bayaran agad ang halagang nakasaad sa Bill Notice upang maiwasan ang agarang pagputol ng inyong serbisyo sa tubig nang walang karagdagang abiso. Maaari nang balewalain ang paalalang ito kung nakapagbayad na sa takdang oras.
          </Text>
        </View>

        <TouchableOpacity style={styles.saveBtn} onPress={handleDownload}>
          <Text style={styles.saveTxt}>Save to Gallery</Text>
        </TouchableOpacity>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  header: {
    height: 60,
    backgroundColor: '#0077B6',
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#ccc',
  },
  backBtn: {
    width: 36,
    height: 36,
    backgroundColor: 'white',
    borderRadius: 6,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollContent: {
    paddingBottom: 120,
  },
  container: {
    backgroundColor: 'white',
    padding: 20,
    margin: 16,
    borderRadius: 12,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
  },
  logo: {
    width: 180,
    height: 180,
    resizeMode: 'contain',
    alignSelf: 'center',
    marginBottom: 16,
  },
  title: { fontSize: 22, fontWeight: 'bold', textAlign: 'center', marginBottom: 4 },
  subTitle: { fontSize: 16, textAlign: 'center', marginBottom: 16 },
  sep: { borderBottomColor: '#ccc', borderBottomWidth: 1, marginVertical: 10 },
  row: { flexDirection: 'row', justifyContent: 'space-between', marginVertical: 4 },
  notice: { fontStyle: 'italic', marginTop: 20, marginBottom: 40, fontSize: 16, textAlign: 'justify' },
  saveBtn: {
    marginTop: 20,
    backgroundColor: '#0077B6',
    paddingVertical: 12,
    marginHorizontal: 16,
    borderRadius: 8,
  },
  saveTxt: { textAlign: 'center', color: 'white', fontSize: 16 },
});
