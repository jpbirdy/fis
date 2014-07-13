#!/bin/bash
#sudo apt-get update
#sudo apt-get install libpcre3 libpcre3-dev libncurses5-dev libxml2-dev libcurl4-openssl-dev

RETVAL=0
FISHOME=$1
clear
echo "Have Fun Using Fis Dev.."
if ["$FISHOME" = ""]
then 
	echo "FISHome is no set!\nUsing install shell like ./install.sh /home/username/fisdev/";
	exit
else 
	echo "FIS HOME is " $FISHOME
fi
#making dirs
echo -n "Now making dirs.."
mkdir -p $FISHOME/packages/ && mkdir -p $FISHOME/tools/
RETVAL=$?
if [ $RETVAL -eq 0 ] 
then
    echo "OK"
else
    echo "Place check your Permission on path :" $FISHOME
    exit
fi

#Unzip files
echo -n "Unzipping files .."
cd packages
#CMD="ls | xargs -t -I {} cp -r {} "$FISHOME"/packages/"
ls | xargs -t -I {} cp -r {} "$FISHOME"/packages/ 1>suc.txt 2>err.txt
RETVAL=$?
if [ $RETVAL -eq 0 ] 
then
    cd $1/packages/
    ls *.tar.gz | xargs -n1 tar xzvf 1>suc.txt 2>err.txt
    echo "Unzip OK!"
else
    echo "Tar command failed!"
    exit
fi


#libxml2
echo -n "Installing libxml2.."
cd libxml2-2.9.0
./configure --prefix=$FISHOME/tools/libxml2 1>suc.txt 2>err.txt
make  1>suc.txt 2>err.txt
make install 1>suc.txt 2>err.txt
RETVAL=$?
if [ $RETVAL -eq 0 ] 
then
    echo "Install libxml2 OK!"
else
    echo "Install failed!"
    exit
fi
make clean 1>suc.txt 2>err.txt
cd ..


#must install g++
echo -n "Installing libmcrypt-2.5.8.."
cd libmcrypt-2.5.8
./configure --prefix=$FISHOME/tools/liblibmcrypt 1>suc.txt 2>err.txt
make  1>suc.txt 2>err.txt
make install 1>suc.txt 2>err.txt
RETVAL=$?
if [ $RETVAL -eq 0 ] 
then
    echo "Install libmcrypt OK!"
else
    echo "Install failed!\nInstalation need C++ or g++!"
    exit
fi
make clean 1>suc.txt 2>err.txt
cd ..


echo -n "Installing zlib-1.2.7.."
cd zlib-1.2.7
./configure --prefix=$FISHOME/tools/zlib 1>suc.txt 2>err.txt
make  1>suc.txt 2>err.txt
make install 1>suc.txt 2>err.txt
RETVAL=$?
if [ $RETVAL -eq 0 ] 
then
    echo "Install zlib OK!"
else
    echo "Install failed!"
    exit
fi
make clean 1>suc.txt 2>err.txt
cd ..

echo -n "Installing libiconv.."
cd libiconv-1.13.1
./configure --prefix=$FISHOME/tools/libiconv 1>suc.txt 2>err.txt
make  1>suc.txt 2>err.txt
make install 1>suc.txt 2>err.txt
RETVAL=$?
if [ $RETVAL -eq 0 ] 
then
    echo "Install libiconv OK!"
else
    echo "Install failed!"
    exit
fi
make clean 1>suc.txt 2>err.txt
cd ..

echo -n "Installing curl.."
cd curl-7.17.1
./configure --prefix=$FISHOME/tools/curl 1>suc.txt 2>err.txt
make  1>suc.txt 2>err.txt
make install 1>suc.txt 2>err.txt
RETVAL=$?
if [ $RETVAL -eq 0 ] 
then
    echo "Install curl OK!"
else
    echo "Install failed!"
    exit
fi
make clean 1>suc.txt 2>err.txt
cd ..

echo -n "Installing php.."
cd php-5.4.11
./configure --prefix=$FISHOME/php  --with-config-file-path=$FISHOME/php/etc  --with-libxml-dir=$FISHOME/tools/libxml2  --with-mcrypt=$FISHOME/tools/liblibmcrypt  --with-iconv=$FISHOME/tools/libiconv  --with-curl=$FISHOME/tools/curl  --enable-soap  --enable-fpm  --enable-mbstring=all  --enable-sockets 1>suc.txt 2>err.txt
# --with-mysql=$1/mysql/ 
# --with-mysqli=$1/mysql/bin/mysql_config 
make  1>suc.txt 2>err.txt
make install 1>suc.txt 2>err.txt
RETVAL=$?
if [ $RETVAL -eq 0 ] 
then
    echo "Install php OK!"
    echo "Copy php conf files ..."
    #cp php.ini-development $FISHOME/php/etc/php.ini
    cp -r $FISHOME/packages/conf/phpconf/* $FISHOME/php/etc/
    cp -r $FISHOME/packages/conf/phplib $FISHOME/php/
    echo "Copy OK"
else
    echo "Install failed!"
    exit
fi
make clean 1>suc.txt 2>err.txt
cd ..

echo -n "Installing yaf.."
cd yaf-2.3.2
$FISHOME/php/bin/phpize 1>suc.txt 2>err.txt
./configure --with-php-config=$FISHOME/php/bin/php-config 1>suc.txt 2>err.txt
make  1>suc.txt 2>err.txt
make install 1>suc.txt 2>err.txt
RETVAL=$?
if [ $RETVAL -eq 0 ] 
then
    echo "Install yaf-2.3.2 OK!"
else
    echo "Install failed!Please check autoconf!"
    exit
fi
make clean 1>suc.txt 2>err.txt
cd ..


#install pcre. ngx need!
echo -n "Installing pcre.."
cd pcre-8.35
./configure --prefix=$FISHOME/tools/pcre 1>suc.txt 2>err.txt
make  1>suc.txt 2>err.txt
make install 1>suc.txt 2>err.txt
RETVAL=$?
if [ $RETVAL -eq 0 ] 
then
    echo "Install pcre OK!"
else
    echo "Install failed!"
    exit
fi
make clean 1>suc.txt 2>err.txt
cd ..


echo -n "Installing nginx.."
cd nginx-1.6.0
./configure --prefix=$FISHOME/nginx --with-pcre=$FISHOME/packages/pcre-8.35 --with-zlib=$FISHOME/packages/zlib-1.2.7 1>suc.txt 2>err.txt
make  1>suc.txt 2>err.txt
make install 1>suc.txt 2>err.txt
RETVAL=$?
if [ $RETVAL -eq 0 ] 
then
    mkdir -p  $FISHOME/webroot
    mkdir -p  $FISHOME/pids
    mkdir -p  $FISHOME/log
    cp $FISHOME/packages/conf/index.php $FISHOME/webroot/index.php
    cp $FISHOME/packages/conf/php-fpm.conf $FISHOME/php/etc/php-fpm.conf
    cp -r $FISHOME/packages/conf/nginxconf/* $FISHOME/nginx/
    echo "Install nginx OK!"
else
    echo "Install failed!"
    cat err.txt
    exit
fi
make clean 1>suc.txt 2>err.txt
cd ..

echo "Install FIS Successfully!"
cp $FISHOME/packages/conf/nginxconf/loadfis.sh $FISHOME/loadfis.sh
cp -r $FISHOME/packages/demo/* $FISHOME/

str="$FISHOME/"
rep=""
for i in `echo "$str" | sed 's/\//\n/g'`
do
    rep=$rep\\\/$i
done

sed -i 's/\/home\/jpbirdy\/fisdev/'$rep'/g' $FISHOME/loadfis.sh
sed -i 's/\/home\/jpbirdy\/fisdev/'$rep'/g' $FISHOME/php/etc/php.ini
sed -i 's/\/home\/jpbirdy\/fisdev/'$rep'/g' $FISHOME/nginx/conf/vhost/php.conf
echo "Please modify some confs...Enjoy it!"
echo "Type localhost:8080/api/api/sample you could see a demo"

#cd mysql-5.0.18
#./configure \
#--prefix=$FISHOME/mysql \
#--enable-thread-safe-client \
#--with-extra-charsets=all  
#make && make install
#make clean
#cd ..


