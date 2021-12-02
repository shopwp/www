import { AccountProvider } from './components/account/_state/provider';
import Bootstrap from './components/bootstrap';

function App() {
  return (
    <AccountProvider>
      <Bootstrap />
    </AccountProvider>
  );
}

export default App;
