#!/bin/bash

FISHOME=/home/jpbirdy/fisdev/
NGINXHOME=$FISHOME/nginx
NGINX=$NGINXHOME/sbin/nginx
PHPHOME=$FISHOME/php
PHPFPMCONF=$PHPHOME/etc/php-fpm.conf
PHPFPMPID=$PHPHOME/var/php-fpm.pid
#logs
echo $NGINX
RETVAL=0

start() {
    echo -n $"Starting nginx: "
    nohup $NGINX >/dev/null 2>&1
    RETVAL=$?
    if [ $RETVAL -eq 0 ]
    then
        echo "OK"
    else
        echo "Failed!"
    fi
    echo -n $"Starting php-fpm: "
    nohup $PHPHOME/sbin/php-fpm -c $PHPHOME/etc/php.ini -y $PHPFPMCONF >/dev/null 2>&1
    if [ $RETVAL -eq 0 ]
    then
        echo "OK"
    else
        echo "Failed!"
    fi
    return $RETVAL
}

stop() {
    echo -n $"Stopping nginx: "
    $NGINX -s stop
    echo $"Stop OK,please check it youself ";
	
    echo -n $"Stopping php-fpm: "
    kill -INT `cat $PHPFPMPID`
    echo  $"Stop OK";
    #return $RETVAL
}

restart() {
    stop
    sleep 2
    start
}


case "$1" in
start)
    start
    ;;

stop)
    stop
    ;;

restart)
    restart
    ;;

reload)
    $NGINX -s reload
    echo  $"reload OK,please check it youself";
    ;;

chkconfig)
    $NGINX -t
    ;;

*)
echo "Usage: $0 {start|stop|restart|chkconfg|reload}"
echo $NGINX
exit 1
esac
