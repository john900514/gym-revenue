const defaultTheme = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');
//add default to colors so we don't always need -500 for base shade
Object.entries(colors).forEach(([name, color]) => color.DEFAULT = color[500]);

module.exports = {
    experimental: {
        applyComplexClasses: true,
    },
    mode: 'jit',
    purge: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    daisyui: {
        themes: ['light', 'dark']
    },
    theme: {
        colors: {
            ...colors,
            primary: colors.blue,
            secondary: colors.red,
            accent: colors.yellow,
            info: colors.sky,
            error: colors.red,
            warning: colors.yellow,
            success: colors.green,
            transparent: 'transparent',
            current: 'currentColor'
        },
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
        },
        spacing: {
            px: '1px',
            0: '0',
            0.5: '0.125rem',
            1: '0.25rem',
            1.5: '0.375rem',
            2: '0.5rem',
            2.5: '0.625rem',
            3: '0.75rem',
            3.5: '0.875rem',
            4: '1rem',
            5: '1.25rem',
            6: '1.5rem',
            7: '1.75rem',
            8: '2rem',
            9: '2.25rem',
            10: '2.5rem',
            11: '2.75rem',
            12: '3rem',
            14: '3.5rem',
            16: '4rem',
            20: '5rem',
            24: '6rem',
            28: '7rem',
            32: '8rem',
            36: '9rem',
            40: '10rem',
            44: '11rem',
            48: '12rem',
            52: '13rem',
            56: '14rem',
            60: '15rem',
            64: '16rem',
            72: '18rem',
            80: '20rem',
            96: '24rem',
        },
    },

    plugins: [
        // require('@tailwindcss/forms'),
        // require('@tailwindcss/typography'),
        require('daisyui')
    ],
};
