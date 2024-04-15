import fs from 'fs';

function readCSV(filename) {
    return fs.readFileSync(filename, 'utf8').trim().split('\n').slice(1);
}

function parseRow(row) {
    const [timestamp, login, check_report, update_name, logout, check_health] = row.split(',');
    const date = new Date(parseInt(timestamp));
    const hour = String(date.getHours()).padStart(2, '0');
    const minute = String(date.getMinutes()).padStart(2, '0');
    const formattedTime = `${hour}:${minute}`;
    return { formattedTime, login, check_report, update_name, logout, check_health };
}

function calculateAverages(rows) {
    const averagesByMinuteAndCategory = {};
    rows.forEach(row => {
        const { formattedTime, login, check_report, update_name, logout, check_health } = parseRow(row);
        const categories = { login, check_report, update_name, logout, check_health };
        Object.entries(categories).forEach(([category, time]) => {
            averagesByMinuteAndCategory[formattedTime] ??= {};
            averagesByMinuteAndCategory[formattedTime][category] ??= [];
            averagesByMinuteAndCategory[formattedTime][category].push(parseInt(time));
        });
    });
    return averagesByMinuteAndCategory;
}

function calculateBaseline(averagesByMinuteAndCategory) {
    const firstKey = Object.keys(averagesByMinuteAndCategory)[0];
    if (!firstKey) return null;

    const categories = averagesByMinuteAndCategory[firstKey];
    const allCategoryTimes = Object.values(categories).flat();
    return allCategoryTimes.reduce((acc, curr) => acc + curr, 0) / allCategoryTimes.length;
}

function calculateResults(averagesByMinuteAndCategory, baselineAverage) {
    const results = {};
    Object.entries(averagesByMinuteAndCategory).forEach(([formattedTime, categories]) => {
        results[formattedTime] = {};
        let passed = true;

        Object.entries(categories).forEach(([category, times]) => {
            const averageTime = times.reduce((acc, curr) => acc + curr, 0) / times.length;
            results[formattedTime][category] = averageTime;
        });

        const allCategoryAverage = Object.values(categories).flat().reduce((acc, curr) => acc + curr, 0) / Object.values(categories).flat().length;
        results[formattedTime].avg = allCategoryAverage;

        if (allCategoryAverage > 2 * baselineAverage) {
            passed = false;
        }

        results[formattedTime].passed = passed;
    });
    return results;
}

function analyzeResults(results) {
    const passFailPattern = Object.values(results).map(result => result.passed ? 'P' : 'F').join('');
    let cleanedPattern = passFailPattern[0];
    for (let i = 1; i < passFailPattern.length; i++) {
        if (passFailPattern[i] !== passFailPattern[i - 1]) {
            cleanedPattern += passFailPattern[i];
        }
    }

    let resultCode = '';
    switch (cleanedPattern) {
        case 'P':
            resultCode = 'PASS';
            console.log('During the experiment, user experience remained acceptable');
            break;
        case 'PF':
            resultCode = 'FAIL';
            console.log('During the experiment, user experience deteriorated and did NOT recover');
            break;
        case 'PFP':
            resultCode = 'PARTIAL';
            console.log('During the experiment, user experience deteriorated but recovered again');
            break;
        default:
            resultCode = 'INCONSISTENT';
            console.log('During the experiment, user experience flipped back and forth between deteriorated and acceptable');
            break;
    }
    console.log('Result Code:', resultCode);
}

function analyseUserExperience(csv) {
    const rows = readCSV(csv);
    const averagesByMinuteAndCategory = calculateAverages(rows);

    const baselineAverage = calculateBaseline(averagesByMinuteAndCategory);
    console.log('Baseline Average:', baselineAverage);

    const results = calculateResults(averagesByMinuteAndCategory, baselineAverage);
    console.log('Results:', results);

    analyzeResults(results);
}

export {
    analyseUserExperience
};
