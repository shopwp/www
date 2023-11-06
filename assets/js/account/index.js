import App from './App.js'

function RenderAccountApp() {
	const container = document.getElementById('root-account')
	const root = wp.element.createRoot(container)

	root.render(<App />)
}

RenderAccountApp()
