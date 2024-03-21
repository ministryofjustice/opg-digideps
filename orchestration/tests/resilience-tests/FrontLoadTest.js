import { Cluster } from 'puppeteer-cluster';
import puppeteer from 'puppeteer';
import fs from 'fs';
import {
  getSecret,
  loginAsUser,
  checkReportSectionsVisible,
  updateUserDetailsConcurrent,
  logOutUser,
  checkServiceHealthFront
} from './../utility/Utility.js';

const url = process.env.FRONT_URL;
const environment = process.env.ENVIRONMENT;
const endpoint = process.env.ENDPOINT;
const filename = 'tests/resilience-tests/task_timings.csv'

// Define your task
async function measureElapsedTime(page, action, csvRow, timeoutValue) {
    const startTime = Date.now();
    await action(page);
    const endTime = Date.now();
    const elapsedTime = endTime - startTime;
    csvRow += `${elapsedTime},`;
    await new Promise(resolve => setTimeout(resolve, timeoutValue));
    return csvRow;
}

async function task({ page, data }) {
    const { url, user, password } = data;
    const timeoutValue = Math.floor(Math.random() * (5000 - 500 + 1)) + 500;
    await new Promise(resolve => setTimeout(resolve, timeoutValue));
    let csvRow = `${Date.now()},`;

    try {
        csvRow = await measureElapsedTime(page, loginAsUser.bind(null, page, url, user, password, 'lay'), csvRow, timeoutValue);
        csvRow = await measureElapsedTime(page, checkReportSectionsVisible.bind(null, page), csvRow, timeoutValue);
        csvRow = await measureElapsedTime(page, updateUserDetailsConcurrent.bind(null, page, '#profile_firstname', '#profile_save'), csvRow, timeoutValue);
        csvRow = await measureElapsedTime(page, logOutUser.bind(null, page, url), csvRow, timeoutValue);
        csvRow = await measureElapsedTime(page, checkServiceHealthFront.bind(null, page, url), csvRow, timeoutValue);

        fs.appendFileSync(filename, csvRow + '\n');
    } catch (error) {
        csvRow = `${Date.now()},10000,10000,10000,10000,10000,`
        fs.appendFileSync(filename, csvRow + '\n');
        console.error('Error in task:', error);
    } finally {
        await page.close();
    }
}

// Create a cluster
(async () => {
    fs.writeFileSync(filename, 'timestamp,login,check_report,update_name,logout,check_health\n');
    const cluster = await Cluster.launch({
        concurrency: Cluster.CONCURRENCY_CONTEXT,
        maxConcurrency: 1, // Number of threads you want to run concurrently
        puppeteer,
        puppeteerOptions: {
            executablePath: '/usr/bin/chromium-browser',
            args: ['--no-sandbox', '--headless']
        },
    });
    console.log(url);
    const { _a, _b, _c, deputy_user, deputy_password } = await getSecret(environment, endpoint);
    const user = deputy_user;
    const password = deputy_password;

    // Start the timer
    const startTime = Date.now();
    const duration = 5 * 60 * 1000; // 5 minutes in milliseconds

    // Loop until the specified duration
    while (Date.now() - startTime < duration) {
        // Define tasks
        for (let i = 0; i < 1; i++) { // Number of threads
            cluster.queue({ url, user, password }, task);
        }
        await cluster.idle(); // Wait for all tasks to finish
        await new Promise(resolve => setTimeout(resolve, 200)); // Wait for 0.2 seconds before starting the next iteration
    }

    await cluster.close(); // Close the cluster after the specified duration
})();
