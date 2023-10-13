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
          "accent": "#f6f9c8",
          "neutral": "#191a3e",
          "base-100": "#ffffff",
          "info": "#cae2e8",
          "success": "#dff2a1",
          "warning": "#f7e488",
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