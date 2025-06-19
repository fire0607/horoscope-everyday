<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

interface HoroscopeData {
  sign: string;
  date: string;
  content: string;
  lucky_color: string | null;
}

const horoscope = ref<HoroscopeData | null>(null);
const isLoading = ref(false);
const errorMessage = ref<string | null>(null);

const fetchHoroscope = async () => {
  isLoading.value = true;
  errorMessage.value = null;
  try {
    const response = await axios.get<HoroscopeData[]>('http://127.0.0.1:8000/api/horoscopes');
    if (response.data.length > 0) {
      horoscope.value = response.data[0]; // 假設我們只顯示最新的運勢
    } else {
      horoscope.value = null;
      errorMessage.value = '目前沒有星座運勢資料，請點擊「取得最新運勢」按鈕。';
    }
  } catch (error) {
    console.error('Error fetching horoscope:', error);
    errorMessage.value = '載入星座運勢失敗，請稍後再試。';
  } finally {
    isLoading.value = false;
  }
};

const fetchLatestHoroscope = async () => {
  isLoading.value = true;
  errorMessage.value = null;
  try {
    await axios.get('http://127.0.0.1:8000/api/horoscope/fetch');
    alert('已送出取得最新運勢請求，請稍候片刻後重新整理頁面或點擊按鈕。');
    await fetchHoroscope(); // 重新載入資料
  } catch (error) {
    console.error('Error fetching latest horoscope:', error);
    errorMessage.value = '取得最新運勢失敗，請檢查後端服務。';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchHoroscope();
});
</script>

<template>
  <div class="horoscope-container">
    <h1>每日星座運勢</h1>

    <div v-if="isLoading" class="loading-message">載入中...</div>
    <div v-else-if="errorMessage" class="error-message">{{ errorMessage }}</div>
    <div v-else-if="horoscope" class="horoscope-card">
      <h2>{{ horoscope.sign }}</h2>
      <p class="date">日期: {{ new Date(horoscope.date).toLocaleDateString('zh-TW', { month: 'numeric', day: 'numeric' }) }}</p>
      <p class="content">{{ horoscope.content }}</p>
      <p v-if="horoscope.lucky_color" class="lucky-color">幸運色: <span :style="{ color: horoscope.lucky_color }">{{ horoscope.lucky_color }}</span></p>
    </div>
    <div v-else class="no-data-message">目前沒有星座運勢資料。</div>

    <button @click="fetchLatestHoroscope" :disabled="isLoading" class="fetch-button">
      {{ isLoading ? '處理中...' : '取得最新運勢' }}
    </button>
  </div>
</template>

<style scoped>
.horoscope-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  font-family: 'Arial', sans-serif;
  background-color: #f4f7f6;
  min-height: 100vh;
}

h1 {
  color: #333;
  margin-bottom: 30px;
}

.horoscope-card {
  background-color: #ffffff;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  padding: 30px;
  margin-bottom: 20px;
  width: 100%;
  max-width: 500px;
  text-align: center;
}

h2 {
  color: #5c677d;
  margin-bottom: 15px;
  font-size: 1.8em;
}

p {
  color: #666;
  line-height: 1.6;
  margin-bottom: 10px;
}

.date {
  font-style: italic;
  color: #888;
}

.content {
  font-size: 1.1em;
  margin-top: 20px;
  white-space: pre-wrap; /* 保持換行 */
}

.lucky-color {
  font-weight: bold;
  margin-top: 15px;
}

.fetch-button {
  background-color: #4CAF50;
  color: white;
  padding: 12px 25px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1em;
  transition: background-color 0.3s ease;
}

.fetch-button:hover {
  background-color: #45a049;
}

.fetch-button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.loading-message,
.error-message,
.no-data-message {
  color: #888;
  font-size: 1.2em;
  margin-top: 20px;
}

.error-message {
  color: #d9534f;
}
</style>
