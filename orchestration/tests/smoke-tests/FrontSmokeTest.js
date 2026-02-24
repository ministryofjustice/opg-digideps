import puppeteer from 'puppeteer';

const url = process.env.FRONT_URL;
const environment = process.env.ENVIRONMENT;
const endpoint = process.env.ENDPOINT;

import {
  getSecret,
  loginAsUser,
  checkReportSectionsVisible,
  updateUserDetails,
  logOutUser,
  checkServiceHealthFront,
  openPageWithRetries
} from './../utility/Utility.js';

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
    const user = deputy_user;
    const password = deputy_password;
    await loginAsUser(page, url, user, password, 'courtorder/');
    await checkReportSectionsVisible(page);
    await updateUserDetails(page, '#profile_firstname', '#profile_save')
    await logOutUser(page, url)
    await checkServiceHealthFront(page, url);
  } catch (error) {
    console.error('Smoke tests failed:', error);
    process.exit(1);
  } finally {
    await browser.close();
  }
};

runSmoke();
