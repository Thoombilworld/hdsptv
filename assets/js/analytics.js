/**
 * Vercel Web Analytics Integration
 * This script initializes Vercel Web Analytics for the HDSPTV News Platform
 */
import { inject } from '../../node_modules/@vercel/analytics/dist/index.mjs';

// Initialize Vercel Analytics
inject({
  mode: 'auto', // Automatically detect environment
  debug: false  // Set to true for debugging in development
});
