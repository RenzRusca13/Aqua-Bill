// app/index.tsx

import AsyncStorage from '@react-native-async-storage/async-storage';
import { useRouter } from 'expo-router';
import React, { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  ImageBackground,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import * as Animatable from 'react-native-animatable';
import Icon from 'react-native-vector-icons/Ionicons';

import Logo from '../assets/images/logo.png';
import Bg from '../assets/images/lupet.jpg';
import { IP_ADDRESS } from './utils/constants';


export default function LoginScreen() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);

  const router = useRouter();

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Required Fields', 'Please enter both email and password.');
      return;
    }

    setLoading(true);
    try {
      const response = await fetch(`${IP_ADDRESS}/aquabill-api/login.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });

      const data = await response.json();

      if (data.status === 'success') {
        const user = data.user; // expecting { id: number, type: 'resident'|'collector' }

        // persist auth info for Notifications screen
        await AsyncStorage.setItem('userId', String(user.id));
        await AsyncStorage.setItem('userType', user.type);

        Alert.alert('Login Successfully!✅');
        setTimeout(() => {
          setLoading(false);

          if (user.type === 'collector') {
            router.replace('/tabs/home');
          } else {
            router.replace('/user');
          }
        }, 500);
      } else {
        setLoading(false);
        Alert.alert('Login Failed!❌', data.message || 'Invalid credentials.');
      }
    } catch (error) {
      setLoading(false);
      Alert.alert('Error!⚠️', 'Cannot connect to the server.');
    }
  };

  return (
    <ImageBackground source={Bg} resizeMode="cover" style={styles.background}>
      <View style={{ height: 25, backgroundColor: 'black', width: '100%' }} />

      <View style={styles.overlay}>
        <Animatable.Image
          animation="fadeIn"
          duration={1000}
          delay={100}
          source={Logo}
          style={styles.logo}
          resizeMode="contain"
        />

        <Animatable.View animation="fadeInUp" delay={300} style={styles.form}>
          <TextInput
            style={styles.input}
            placeholder="Email"
            placeholderTextColor="#9ca3af"
            value={email}
            onChangeText={setEmail}
            keyboardType="email-address"
            autoCapitalize="none"
          />

          <View style={{ position: 'relative' }}>
            <TextInput
              style={[styles.input, { paddingRight: 44 }]}
              placeholder="Password"
              placeholderTextColor="#9ca3af"
              secureTextEntry={!showPassword}
              value={password}
              onChangeText={setPassword}
            />
            <TouchableOpacity
              onPress={() => setShowPassword(!showPassword)}
              style={styles.eyeIcon}
            >
              <Icon name={showPassword ? 'eye-off' : 'eye'} size={22} color="#0077B6" />
            </TouchableOpacity>
          </View>

          {loading ? (
            <ActivityIndicator size="large" color="#0077B6" style={{ marginTop: 20 }} />
          ) : (
            <>
              <TouchableOpacity onPress={handleLogin} style={styles.loginButton}>
                <Text style={styles.loginButtonText}>Log In</Text>
              </TouchableOpacity>

              <TouchableOpacity>
                <Text style={styles.forgotText}>Forgot Password?</Text>
              </TouchableOpacity>
            </>
          )}
        </Animatable.View>
      </View>

      <View style={{ height: 45, backgroundColor: 'black', width: '100%' }} />
    </ImageBackground>
  );
}

const styles = StyleSheet.create({
  background: {
    flex: 1,
  },
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 24,
  },
  logo: {
    width: 250,
    height: 250,
    marginBottom: 12,
  },
  form: {
    width: '100%',
  },
  input: {
    backgroundColor: '#fff',
    padding: 14,
    borderRadius: 10,
    fontSize: 16,
    borderWidth: 1,
    borderColor: '#ccc',
    marginBottom: 16,
    color: '#111827',
  },
  eyeIcon: {
    position: 'absolute',
    right: 16,
    top: 16,
  },
  loginButton: {
    backgroundColor: '#0077B6',
    paddingVertical: 14,
    borderRadius: 10,
    alignItems: 'center',
    marginBottom: 16,
  },
  loginButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 18,
  },
  forgotText: {
    color: '#fff',
    textAlign: 'center',
    textDecorationLine: 'underline',
    fontSize: 14,
  },
});
