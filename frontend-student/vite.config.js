import { defineConfig, loadEnv } from 'vite'

export default ({ mode }) => {
  const env = loadEnv(mode, process.cwd())
  const HOST = env.VITE_HOST

  return defineConfig({
    server: {
      proxy: {
        '/api': {
          target: HOST,
          changeOrigin: true,
          secure: false,
          cookieDomainRewrite: { '*': '' }
        }
      }
    }
  })
}