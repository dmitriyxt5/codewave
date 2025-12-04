<script setup>
import { onMounted, ref } from 'vue'

const ready = ref(false)
const lang = ref('ru')

// Загружаем скрипт Google
const loadScript = () =>
	new Promise((resolve) => {
		if (window.google && window.google.translate) return resolve()
		const s = document.createElement('script')
		s.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'
		window.googleTranslateElementInit = () => resolve()
		document.head.appendChild(s)
	})

// Применяем выбранный язык
const applyLang = (l) => {
	const sel = document.querySelector('.goog-te-combo')
	if (!sel) return
	sel.value = l
	sel.dispatchEvent(new Event('change'))
}

// Сохранение языка + применение
const setLang = (l) => {
	lang.value = l
	localStorage.setItem('gt_lang', l)
	applyLang(l)
}

const toggle = () => {
	setLang(lang.value === 'ru' ? 'en' : 'ru')
}

// Инициализация переводчика
const init = () => {
	new window.google.translate.TranslateElement(
		{ pageLanguage: 'ru', includedLanguages: 'en,ru', autoDisplay: false },
		'google_translate_element'
	)

	const saved = localStorage.getItem('gt_lang') || 'ru'
	lang.value = saved
	applyLang(saved)
	ready.value = true
}

// Функция, удаляющая баннер и сдвиги
const fixGoogleTranslate = () => {
	// Google пытается двигать body вниз — запрещаем
	document.body.style.top = '0px'

	// Баннер Google (iframe)
	const frame = document.querySelector('.goog-te-banner-frame')
	if (frame) frame.style.display = 'none'

	// Popup-подсказка
	const balloon = document.querySelector('#goog-gt-tt')
	if (balloon) balloon.style.display = 'none'
}

onMounted(async () => {
	await loadScript()
	init()

	// MutationObserver следит за любыми вставками Google
	const observer = new MutationObserver(() => fixGoogleTranslate())
	observer.observe(document.documentElement, {
		childList: true,
		subtree: true
	})

	// На старте тоже принудительно фиксируем
	fixGoogleTranslate()
})
</script>

<template>
	<div class="inline-flex items-center gap-2">
		<button
			type="button"
			class="px-3 py-1.5 rounded-md border border-gray-200 bg-gray-50 hover:bg-gray-100"
			@click="toggle"
			:disabled="!ready"
		>
			{{ lang === 'ru' ? 'EN' : 'RU' }}
		</button>

		<div id="google_translate_element" class="hidden"></div>
	</div>
</template>

<style>
/* Убрать логотип, картинку, лишний мусор */
.goog-logo-link {
	display: none !important;
}
.goog-te-gadget img {
	display: none !important;
}

/* Полностью скрываем баннер */

.goog-te-banner-frame,
iframe[class*='VIpgJd-ZVi9od'] {
	position: relative !important;
	top: 0 !important;
	left: 0 !important;
	width: 100% !important;
	z-index: auto !important;
}

/* Скрываем всплывающую подсказку */
#goog-gt-tt,
.goog-te-balloon-frame {
	display: none !important;
}

/* Перегружаем fontsize, чтобы не ломал верстку */
.goog-te-gadget {
	font-size: 0 !important;
}

.goog-te-combo {
	font-size: 14px !important;
	padding: 0;
	margin: 0;
	border: 0;
	background: transparent;
}

/* Google иногда добавляет top: 40px к body */
body {
	top: 0 !important;
}
</style>
