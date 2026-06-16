import { createTheme } from '@mui/material/styles';

const theme = createTheme({
  palette: {
    mode: 'light',
    primary: {
      main: '#1A365D', // Deep navy blue
      light: '#2B6CB0',
      dark: '#0A2440',
      contrastText: '#FFFFFF',
    },
    secondary: {
      main: '#ED8936', // Vibrant orange accent for construction/service
      light: '#F6AD55',
      dark: '#C05621',
      contrastText: '#FFFFFF',
    },
    background: {
      default: '#F7FAFC', // Light gray background
      paper: '#FFFFFF',
    },
    text: {
      primary: '#2D3748',
      secondary: '#718096',
    },
    error: {
      main: '#E53E3E',
    },
    success: {
      main: '#38A169',
    },
    warning: {
      main: '#DD6B20',
    },
    info: {
      main: '#3182CE',
    },
  },
  typography: {
    fontFamily: '"Inter", "Roboto", "Helvetica", "Arial", sans-serif',
    h1: {
      fontWeight: 700,
      fontSize: '2.5rem',
    },
    h2: {
      fontWeight: 600,
      fontSize: '2rem',
    },
    h3: {
      fontWeight: 600,
      fontSize: '1.75rem',
    },
    h4: {
      fontWeight: 600,
      fontSize: '1.5rem',
    },
    h5: {
      fontWeight: 500,
      fontSize: '1.25rem',
    },
    h6: {
      fontWeight: 500,
      fontSize: '1rem',
    },
    button: {
      fontWeight: 600,
      textTransform: 'none',
    },
  },
  shape: {
    borderRadius: 8,
  },
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          borderRadius: 8,
          boxShadow: 'none',
          '&:hover': {
            boxShadow: '0px 4px 12px rgba(0, 0, 0, 0.1)',
          },
        },
      },
    },
    MuiCard: {
      styleOverrides: {
        root: {
          borderRadius: 12,
          boxShadow: '0px 2px 10px rgba(0, 0, 0, 0.05)',
          transition: 'box-shadow 0.3s ease-in-out, transform 0.3s ease-in-out',
          '&:hover': {
            boxShadow: '0px 6px 20px rgba(0, 0, 0, 0.1)',
            transform: 'translateY(-2px)',
          },
        },
      },
    },
  },
});

export default theme;
