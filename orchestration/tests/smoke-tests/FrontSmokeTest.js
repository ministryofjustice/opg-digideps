import puppeteer from 'puppeteer';

const url = process.env.FRONT_URL;

import { checkServiceHealthFront } from './../utility/Utility.js';

const runSmoke = async () => {
  const browser = await puppeteer.launch(
    {
      executablePath: '/usr/bin/chromium-browser',
      args: ['--no-sandbox', '--headless']
    });
  const page = await browser.newPage();

  try {
    await checkServiceHealthFront(page, url);
  } catch (error) {
    console.error('Smoke tests failed:', error);
  } finally {
    await browser.close();
  }
};

runSmoke();
