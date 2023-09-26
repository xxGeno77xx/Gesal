import colors from 'tailwindcss/colors' 
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography' 
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
        './vendor/filament/**/*.blade.php', 
  ],
  darkMode: 'class',
  theme: {
    extend: {
        colors: { 
          danger: colors.red,
          primary: colors.sky,
          success: colors.green,
          warning: colors.amber,


      }, 
    },
  },
  plugins: [
        forms, 
        typography,
  ],
}

