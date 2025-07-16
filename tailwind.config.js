import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'

/** @type {import('tailwindcss').Config} */
export default {
	content: [
		'./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
		'./storage/framework/views/*.php',
		'./resources/views/**/*.blade.php',
		'./resources/js/**/*.vue'
	],

	theme: {
		extend: {
			fontFamily: {
				sans: ['Figtree', ...defaultTheme.fontFamily.sans]
			},
			boxShadow: {
				1: '0 0px 0px 0px rgb(217 217 217 / 0.4), 0 0px 21px 0px rgb(217 217 217 / 0.4)'
			}
		}
	},

	plugins: [forms]
}
