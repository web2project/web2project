var commonPasswords = new Array('password', 'pass', '1234', '1246', 'test',
                                'qwerty', '123456', 'q1w2e3r4', 'password1');

var numbers = "0123456789";
var lowercase = "abcdefghijklmnopqrstuvwxyz";
var uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
var punctuation = "!.@$£#*()%~<>{}[]";

function checkPassword(password) {

    var combinations = 0;

    if (contains(password, numbers) > 0) {
        combinations += 10;
    }

    if (contains(password, lowercase) > 0) {
        combinations += 26;
    }

    if (contains(password, uppercase) > 0) {
        combinations += 26;
    }

    if (contains(password, punctuation) > 0) {
        combinations += punctuation.length;
    }

    // work out the total combinations
    var totalCombinations = Math.pow(combinations, password.length);

    // if the password is a common password, then everthing changes...
    if (isCommonPassword(password)) {
        totalCombinations = 75000 // about the size of the dictionary
    }

    // work out how long it would take to crack this (@ 200 attempts per second)
    var timeInSeconds = (totalCombinations / 200) / 2;

    // this is how many days? (there are 86,400 seconds in a day.
    var timeInDays = timeInSeconds / 86400

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

function isCommonPassword(password) {
    for (var i = 0; i < commonPasswords.length; i++) {
        var commonPassword = commonPasswords[i];
        if (password == commonPassword) {
            return true;
        }
    }

    return false;
}

function contains(password, validChars) {
    var count = 0;

    for (var i = 0; i < password.length; i++) {
        var chr = password.charAt(i);
        if (validChars.indexOf(chr) > -1) {
            count++;
        }
    }

    return count;
}