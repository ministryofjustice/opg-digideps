import puppeteer from 'puppeteer';
import {
  getSecret,
  loginAsUser,
  searchForUser,
  searchForClient,
  checkOrganisations,
  checkSubmissions,
  checkAnalytics,
  updateUserDetails,
  logOutUser,
  checkServiceHealthAdmin,
  openPageWithRetries
} from './../utility/Utility.js';

const url = process.env.ADMIN_URL;
const environment = process.env.ENVIRONMENT;
const endpoint = process.env.ENDPOINT;

const runSmoke = async () => {
  const browser = await puppeteer.launch({
    executablePath: '/usr/bin/chromium-browser',
    headless: true,
    protocolTimeout: 30000,
    args: [
      '--no-sandbox',
      '--disable-dev-shm-usage',
      '--no-first-run',
      '--no-default-browser-check',
      '--disable-background-networking',
      '--disable-background-timer-throttling',
      '--disable-backgrounding-occluded-windows',
      '--disable-breakpad',
      '--disable-client-side-phishing-detection',
      '--disable-component-update',
      '--disable-default-apps',
      '--disable-features=AutofillServerCommunication,AutofillDomainReliability,AutofillPredictionImprovements,PasswordManagerExtension,AccountConsistency,OptimizationGuideModelDownloading,InterestFeedContentSuggestions',
      '--disable-hang-monitor',
      '--disable-ipc-flooding-protection',
      '--disable-popup-blocking',
      '--disable-prompt-on-repost',
      '--disable-renderer-backgrounding',
      '--disable-sync',
      '--metrics-recording-only',
      '--safebrowsing-disable-auto-update',
      '--safebrowsing-disable-download-protection',
      '--window-size=1280,800',
      '--host-resolver-rules=MAP accounts.google.com 127.0.0.1,MAP content-autofill.googleapis.com 127.0.0.1'
    ],
  });

  const version = await browser.version();
  console.log(`Running Chromium version: ${version}`);

  const page = await openPageWithRetries(browser);

  try {
    const { admin_user, admin_password, client, deputy_user, deputy_password } = await getSecret(environment, endpoint);
    const user = admin_user;
    const password = admin_password;
    await loginAsUser(page, url, user, password, 'admin');
    await searchForUser(page, user);
    await searchForClient(page, client);
    await checkOrganisations(page);
    await checkSubmissions(page);
    await checkAnalytics(page);
    await updateUserDetails(page, '#user_details_firstname', '#user_details_save');
    await logOutUser(page, url)
    await checkServiceHealthAdmin(page, url);
  } catch (error) {
    console.error('Smoke tests failed:', error);
    process.exit(1);
  } finally {
    await browser.close();
  }
};

runSmoke();
