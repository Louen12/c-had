import eslintConfigPrettier from 'eslint-config-prettier'

export default [
  {
    ignores: ['.nuxt/', '.output/', 'node_modules/', 'dist/'],
  },
  eslintConfigPrettier,
]
