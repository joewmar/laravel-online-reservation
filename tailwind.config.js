/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  daisyui: {
    themes: [
      {
        mytheme: {
          "primary": "#409122",
          "secondary": "#e9e92f",
          "accent": "#ecfccb",
          "neutral": "#191a3e",
          "base-100": "#ffffff",
          "info": "#22d3ee",
          "success": "#a3e635",  
          "warning": "#facc15", 
          "error": "#ef4444",
        },
      },
    ],
  },
  plugins: [
    require("@tailwindcss/typography"),
    require('@tailwindcss/forms'),
    require("daisyui"),
  ],
}