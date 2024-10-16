import terser from '@rollup/plugin-terser';
import copyright from '../../../../../../scripts/Copyright-Checker/copyright';
import preserveCopyright from '../../../../../../scripts/Copyright-Checker/preserveCopyright';

export default {
  external: [
    'jquery',
    'ilias',
    'document',
  ],
  input: './src/drilldown.js',
  output: {
    file: './dist/drilldown.js',
    format: 'iife',
    banner: copyright,
    globals: {
      document: 'document',
      ResizeObserver: 'ResizeObserver',
      jquery: '$',
      ilias: 'il',
    },
    plugins: [
      terser({
        format: {
          comments: preserveCopyright,
        },
      }),
    ],
  },
};
