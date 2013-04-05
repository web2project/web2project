// taken from a public list of most common passwords
var commonPasswords = new Array('pass', 'word', '123', 'test', 'abc', 'azert', 'monkey', 'dragon',
                                'baseball', 'iloveyou', 'trustno', 'sunshine', 'master',
                                'welcome', 'shadow', 'football', 'jesus', 'ninja', 'mustang',
                                'qwert', 'abc', 'letmein', 'q1w2e3', 'asdf', 'zxcv');

function checkPassword(password, user) {

	// strip common parts so they don't take part in the calculation
    for (var i = 0; i < commonPasswords.length; i++) {
	    password = stripcommon(password, commonPasswords[i]);
    }
	password = stripcommon(password, user);

    var combinations = 0;

    if (/[0-9]/.test(password)) {
        combinations += 10;
    }

    if (/[a-z]/.test(password)) {
        combinations += 26;
    }

    if (/[A-Z]/.test(password)) {
        combinations += 26;
    }

    if (/[ -/:-@[-`{-~]/.test(password)) {
        combinations += 33;
    }

    if (/[^\x00-\x7f]/.test(password)) {
        combinations += 128;
    }

    // work out the total combinations
    var totalCombinations = Math.pow(combinations, password.length);

    // work out how long it would take to crack this (@ 1M attempts per second)
    var timeInSeconds = totalCombinations / 1000000;

    // this is how many days? (there are 86,400 seconds in a day.
    var timeInDays = timeInSeconds / 86400;

    // how long we want it to last
    var lifetime = 365;

    // how close is the time to the projected time?
    var percentage = timeInDays / lifetime;

    var friendlyPercentage = cap(Math.round(percentage * 100), 100);

    if (totalCombinations != 75000 && friendlyPercentage < (password.length * 5)) {
        friendlyPercentage += password.length * 5;
    }

    if(friendlyPercentage > 100) {
        friendlyPercentage = 100;
    }

    var progressBar = document.getElementById("progressBar");
    progressBar.style.width = friendlyPercentage + "%";

    if (percentage > 1) {
        // strong password
        progressBar.style.backgroundColor = "#3bce08";
        return;
    }

    if (percentage > 0.5) {
        // reasonable password
        progressBar.style.backgroundColor = "#ffd801";
        return;
    }

    if (percentage > 0.10) {
        // weak password
        progressBar.style.backgroundColor = "orange";
        return;
    }

    // useless password!
    if (percentage <= 0.10) {
        progressBar.style.backgroundColor = "red";
        return;
    }
}

function cap(number, max) {
    if (number > max) {
        return max;
    } else {
        return number;
    }
}

function stripcommon(password, str, reverse) {
	var index = password.indexOf(str);
	if (index >= 0) {
		password = password.substr(0, index) + password.substr(index+str.length);
	}
	if (!reverse) return stripcommon(password, str.split(/(?:)/).reverse().join(""), true);
	return password;
}
