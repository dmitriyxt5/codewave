<script setup>
import { onMounted, ref } from 'vue'

const ready = ref(false)
const lang = ref('ru')

const loadScript = () =>
	new Promise((resolve) => {
		if (window.google && window.google.translate) return resolve()
		const s = document.createElement('script')
		s.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'
		window.googleTranslateElementInit = () => resolve()
		document.head.appendChild(s)
	})

const applyLang = (l) => {
	const sel = document.querySelector('.goog-te-combo')
	if (!sel) return
	sel.value = l
	sel.dispatchEvent(new Event('change'))
}

const setLang = (l) => {
	lang.value = l
	localStorage.setItem('gt_lang', l)
	applyLang(l)
}

const toggle = () => {
	setLang(lang.value === 'ru' ? 'en' : 'ru')
}

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

onMounted(async () => {
	await loadScript()
	init()
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
.goog-logo-link {
	display: none !important;
}
.goog-te-gadget img {
	display: none !important;
}
#goog-gt-tt,
.goog-te-banner-frame,
.goog-te-balloon-frame {
	display: none !important;
}
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
body {
	top: 0 !important;
}
</style>
