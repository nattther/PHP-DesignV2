/** @type {import('tailwindcss').Config} */
export default {
  content: ["../public/**/*.php","../public/*.php","./src/**/*.ts","../src/**/*.php"],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Noto Sans"', 'system-ui', 'sans-serif'], // default stack
        'noto-sans': ['"Noto Sans"', 'sans-serif'],
      },
      letterSpacing: {
        tightest: '-.075em',
        tighter: '-.05em',
        tight: '-.025em',
        normal: '0',
        wide: '.025em',
        wider: '.05em',
        widest: '.1em',
        'widest-2': '.25em',
        '1': '1px',
        '2': '2px',
        '4': '4px',
      },
      colors: {
        // Semantic palette driven by CSS variables (RGB channels -> preserves /<alpha-value>).
        primary: {
          DEFAULT: 'rgb(var(--primary) / <alpha-value>)',
          foreground: 'rgb(var(--primary-foreground) / <alpha-value>)',
          ring: 'rgb(var(--primary-ring) / <alpha-value>)',
        },
        secondary: {
          DEFAULT: 'rgb(var(--secondary) / <alpha-value>)',
          foreground: 'rgb(var(--secondary-foreground) / <alpha-value>)',
        },
        surface: {
          DEFAULT: 'rgb(var(--surface) / <alpha-value>)',
          foreground: 'rgb(var(--surface-foreground) / <alpha-value>)',
          muted: 'rgb(var(--surface-muted) / <alpha-value>)',
          border: 'rgb(var(--surface-border) / <alpha-value>)',
        },
        // Keep your brand extended set
        'lyreco-green': '#9ac31c',
        'lyreco-green-hover': '#7c9e16',
        'lyreco-blue': '#2d2e87',
        'lyreco-blue-hover': '#12134b',
        'lyreco-dark': '#011627',
        'lyreco-dark-gray': '#51616f',
        'lyreco-dark-medium-gray': '#3e4347',
        'lyreco-gray': '#9facb6',
        'lyreco-light-gray': '#d4dae0',
        'lyreco-dark-white': '#f6f7f8',
        'lyreco-white': '#ffffff',
        'lyreco-error': '#e71d36',
        'lyreco-information': '#5d96c6',
        'lyreco-success': '#84b91c',
        'lyreco-warning': '#ef9d11',
        'lyreco-error-dark': '#aa0a0a',
        'lyreco-information-dark': '#05508f',
        'lyreco-success-dark': '#196000',
        'lyreco-warning-dark': '#952c00',
        'lyreco-error-light': '#fff3f2',
        'lyreco-information-light': '#e7f5f8',
        'lyreco-success-light': '#eaf6ed',
        'lyreco-warning-light': '#fff7e9',
      },
      height: {
        'screen-4/5': '80vh',
        'screen-1/2': '50vh',
        'screen-9/1O': '90vh',
      },
      maxHeight: {
        'screen-4/5': '80vh',
        'screen-1/2': '50vh',
        'screen-9/1O': '90vh',
      }
    },
  },
  plugins: [
    // Optional but recommended for better form baselines
    // require('@tailwindcss/forms'),
  ],
  safelist: [
    // common combos you might toggle dynamically
    "bg-primary","text-primary-foreground","border-primary","ring-primary",
    "bg-secondary","text-secondary-foreground",
    "bg-lyreco-success","text-lyreco-white",
    "bg-lyreco-information",
    "bg-lyreco-warning","text-lyreco-dark",
  ],
}
