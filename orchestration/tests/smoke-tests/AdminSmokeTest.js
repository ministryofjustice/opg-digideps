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
  const browser = await puppeteer.launch(
    {
      executablePath: '/usr/bin/chromium-browser',
      args: [
        '--no-sandbox',
        '--disable-background-networking',
        '--disable-background-timer-throttling',
        '--disable-backgrounding-occluded-windows',
        '--disable-breakpad',
        '--disable-client-side-phishing-detection',
        '--disable-component-update',
        '--disable-default-apps',
        '--disable-dev-shm-usage',
        '--disable-domain-reliability',
        '--disable-features=AudioServiceOutOfProcess,IsolateOrigins,site-per-process,NetworkService,NetworkServiceInProcess,OptimizationHints,TranslateUI,AutofillServerCommunication',
        '--disable-hang-monitor',
        '--disable-ipc-flooding-protection',
        '--disable-notifications',
        '--disable-popup-blocking',
        '--disable-prompt-on-repost',
        '--disable-renderer-backgrounding',
        '--disable-sync',
        '--disable-features=SafeBrowsingEnhancedProtection,SafetyTips',
        '--safe-browsing-disable-auto-update',
        '--metrics-recording-only',
        '--mute-audio',
        '--no-default-browser-check',
        '--no-first-run',
        '--no-service-autorun',
        '--password-store=basic',
        '--use-mock-keychain'
      ],
      protocolTimeout: 30000
    });

  const version = await browser.version();
  console.log(`Running Chromium version: ${version}`);

  const page = await openPageWithRetries(browser);

  await page.setRequestInterception(true);


    page.removeAllListeners('request');   // <– ensure no previous listeners exist

    page.on('request', req => {
      const url = req.url();
      const googleRegex = /(google|googleapis|gvt1|googletagmanager|google-analytics)\.com/i;

      if (googleRegex.test(url)) {
        console.log("🚫 BLOCKED:", url);
        return req.abort();
      }

      console.log("→", req.method(), url);
      return req.continue();
    });


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
