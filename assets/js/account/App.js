import { AccountProvider } from './components/account/_state/provider.jsx'
import { BrowserRouter } from 'react-router-dom'
import Bootstrap from './components/bootstrap/index.jsx'

function App() {
	return (
		<AccountProvider>
			<BrowserRouter basename='/account'>
				<Bootstrap />
			</BrowserRouter>
		</AccountProvider>
	)
}

export default App
