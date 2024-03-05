const puppeteer = require('puppeteer');

const url = process.env.FRONT_URL;

const checkTextInElement = (expectedText, actualText) => {
    if (actualText.includes(expectedText)) {
      console.log(`The element contains "${expectedText}".`);
    } else {
      console.error(`The element does not contain "${expectedText}". Actual text: ${actualText}`);
    }
  };

const checkServiceHealth = async (page) => {
    console.log('===== Step 1: Check service health =====');
    await page.goto(url + '/health-check/service')
    const healthText = await page.$eval('body', body => body.textContent.replace(/\s+/g, '').trim());
    checkTextInElement('Api:OK', healthText);
    checkTextInElement('Redis:OK', healthText);
    checkTextInElement('ClamAV:OK', healthText);
    checkTextInElement('htmlToPdf:OK', healthText);
};

const runSmoke = async () => {
  const browser = await puppeteer.launch(
    {
      executablePath: '/usr/bin/chromium-browser',
      args: ['--no-sandbox', '--headless']
    });
  const page = await browser.newPage();

  try {
    await checkServiceHealth(page);
  } catch (error) {
    console.error('Smoke tests failed:', error);
  } finally {
    await browser.close();
  }
};

runSmoke();
