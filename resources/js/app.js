import './bootstrap';

import { createApp } from 'vue'
import Home from './components/Home.vue'
import AddUrl from "./components/AddUrl.vue";

const app = createApp({})

app.component('home', Home)
app.component('add-url', AddUrl)

app.mount('#vue')
