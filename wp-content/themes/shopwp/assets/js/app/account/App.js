import { AccountProvider } from './components/account/_state/provider'
import { BrowserRouter } from 'react-router-dom'
import Bootstrap from './components/bootstrap'

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
