// Your other imports
import { IP_ADDRESS } from '@/app/utils/constants';
import DateTimePicker from '@react-native-community/datetimepicker';
import { router, useLocalSearchParams } from 'expo-router';
import { useEffect, useState } from 'react';
import {
  Alert,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import Ionicons from 'react-native-vector-icons/Ionicons';

export default function AddBillScreen() {
  const { id } = useLocalSearchParams();
  const [resident, setResident] = useState<{ name: string; meter_no: string } | null>(null);
  const [coverageFrom, setCoverageFrom] = useState<Date | null>(null);
  const [coverageTo, setCoverageTo] = useState<Date | null>(null);
  const [dueDate, setDueDate] = useState<Date | null>(null);
  const [consumption, setConsumption] = useState('');
  const [showPicker, setShowPicker] = useState({ field: '', visible: false });

  const pricePerCubic = 15;

  useEffect(() => {
    const fetchResident = async () => {
      try {
        const res = await fetch(`${IP_ADDRESS}/aquabill-api/residents.php?id=${id}`);
        const json = await res.json();
        if (json.status === 'success') {
          setResident(json.data);
        } else {
          Alert.alert('Error', json.message || 'Failed to load resident.');
        }
      } catch (err) {
        console.error('Failed to load resident', err);
        Alert.alert('Error', 'Server connection failed.');
      }
    };
    fetchResident();
  }, []);

  const handleDateChange = (event, selectedDate: Date | undefined) => {
    if (!selectedDate) {
      setShowPicker({ field: '', visible: false });
      return;
    }

    switch (showPicker.field) {
      case 'from':
        setCoverageFrom(selectedDate);
        break;
      case 'to':
        setCoverageTo(selectedDate);
        break;
      case 'due':
        setDueDate(selectedDate);
        break;
    }
    setShowPicker({ field: '', visible: false });
  };

  const formatDate = (date: Date | null, placeholder: string) =>
    date ? date.toLocaleDateString() : placeholder;

  const calculateTotal = () => {
    const usage = parseFloat(consumption);
    if (isNaN(usage)) return '0.00';
    if (usage <= 10) return '150.00';
    return (usage * pricePerCubic).toFixed(2);
  };

  const handleSave = async () => {
    if (!coverageFrom || !coverageTo || !dueDate || !consumption) {
      Alert.alert('Error', 'Please fill out all fields.');
      return;
    }

    const billData = {
      resident_id: id,
      coverage_from: coverageFrom.toISOString().split('T')[0],
      coverage_to: coverageTo.toISOString().split('T')[0],
      reading_date: coverageTo.toISOString().split('T')[0],
      due_date: dueDate.toISOString().split('T')[0],
      consumption: parseFloat(consumption),
      price_per_cubic: pricePerCubic,
      total: parseFloat(calculateTotal()),
    };

    try {
      const res = await fetch(`${IP_ADDRESS}/aquabill-api/add-bill.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(billData),
      });
      const json = await res.json();

      if (json.status === 'success') {
        Alert.alert('Success', 'Bill added successfully.');
        router.back();
      } else {
        Alert.alert('Error', json.message || 'Failed to save');
      }
    } catch (error) {
      Alert.alert('Error', 'Server error.');
    }
  };

  return (
    <View style={{ flex: 1, backgroundColor: 'white' }}>
      <View style={styles.topBar}>
        <TouchableOpacity style={styles.backButton} onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={22} color="#0077B6" />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.formContainer}>
        {/* All Inputs and Buttons */}
        <Text style={styles.label}>Coverage Date From</Text>
        <TouchableOpacity style={styles.input} onPress={() => setShowPicker({ field: 'from', visible: true })}>
          <Text style={[styles.inputText, !coverageFrom && styles.placeholderText]}>
            {formatDate(coverageFrom, 'Select Date')}
          </Text>
        </TouchableOpacity>

        <Text style={styles.label}>Coverage Date To</Text>
        <TouchableOpacity style={styles.input} onPress={() => setShowPicker({ field: 'to', visible: true })}>
          <Text style={[styles.inputText, !coverageTo && styles.placeholderText]}>
            {formatDate(coverageTo, 'Select Date')}
          </Text>
        </TouchableOpacity>

        <Text style={styles.label}>Reading Date</Text>
        <View style={styles.input}>
          <Text style={styles.inputText}>{coverageTo ? coverageTo.toLocaleDateString() : ''}</Text>
        </View>

        <Text style={styles.label}>Due Date</Text>
        <TouchableOpacity style={styles.input} onPress={() => setShowPicker({ field: 'due', visible: true })}>
          <Text style={[styles.inputText, !dueDate && styles.placeholderText]}>
            {formatDate(dueDate, 'Select Date')}
          </Text>
        </TouchableOpacity>

        <Text style={styles.label}>Consumption (m³)</Text>
        <TextInput
          style={styles.textInput}
          placeholder="Enter consumption"
          keyboardType="numeric"
          value={consumption}
          onChangeText={setConsumption}
        />

        <Text style={styles.label}>Total Amount (₱)</Text>
        <View style={styles.input}>
          <Text style={styles.inputText}>₱ {calculateTotal()}</Text>
        </View>

        <TouchableOpacity style={styles.saveButton} onPress={handleSave}>
          <Text style={styles.saveButtonText}>Save</Text>
        </TouchableOpacity>
      </ScrollView>

      {showPicker.visible && (
        <DateTimePicker
          value={new Date()}
          mode="date"
          display={Platform.OS === 'ios' ? 'spinner' : 'default'}
          onChange={handleDateChange}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  topBar: {
    height: 60,
    backgroundColor: '#0077B6',
    justifyContent: 'center',
    paddingHorizontal: 16,
  },
  backButton: {
    width: 36,
    height: 36,
    backgroundColor: 'white',
    borderRadius: 6,
    justifyContent: 'center',
    alignItems: 'center',
  },
  formContainer: {
    padding: 20,
    paddingBottom: 120, // ✅ To make sure the button is scrollable above the tab bar
  },
  label: {
    fontSize: 16,
    marginBottom: 6,
    color: '#333',
  },
  input: {
    backgroundColor: '#f3f4f6',
    padding: 12,
    borderRadius: 8,
    marginBottom: 20,
  },
  inputText: {
    fontSize: 16,
    color: '#111827',
  },
  placeholderText: {
    color: '#9ca3af',
  },
  textInput: {
    backgroundColor: '#f3f4f6',
    padding: 12,
    borderRadius: 8,
    fontSize: 16,
    color: '#111827',
    marginBottom: 20,
  },
  saveButton: {
    backgroundColor: '#0077B6',
    paddingVertical: 14,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 10,
  },
  saveButtonText: {
    color: 'white',
    fontSize: 18,
    fontWeight: 'bold',
  },
});
