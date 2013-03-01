For use remind events need:
chmod +x /path/to/repository/modules/calendar/inCronForNotifyEvent.php
sudo echo "* * * * * root  cd /path/to/repository/modules/calendar/ && ./inCornForNotifyEvent.php" >> /etc/cron.d/web2project

and add to includes/config.php string:

$w2Pconfig['mailServer']='mailServ.com';

there "mailServ.com" is your server;