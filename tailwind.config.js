import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.blade.php',
         './resources/**/*.js', 
         './resources/**/*.vue' 
        
    ],

    theme: {
        extend: {
            fontFamily: {
                    colors: { brand: "#1E40AF", 
                        accent: "rgb(197, 134, 26)", 
                        neutral: "rgb(216, 10, 62)" 
                        },
                     sans: ["Inter", "sans-serif"],
            },
        },
    },
    plugins: [], 

  }
