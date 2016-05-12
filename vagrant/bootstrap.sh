#!/usr/bin/env bash

apt-get update

## Ruby
apt-get install -y git-core curl zlib1g-dev build-essential libssl-dev libreadline-dev libyaml-dev libsqlite3-dev sqlite3 libxml2-dev libxslt1-dev libcurl4-openssl-dev python-software-properties libffi-dev

cd ~
git clone git://github.com/sstephenson/rbenv.git .rbenv
echo 'export PATH="$HOME/.rbenv/bin:$PATH"' >> ~/.bashrc
echo 'eval "$(rbenv init -)"' >> ~/.bashrc
exec $SHELL

cd ~
git clone git://github.com/sstephenson/ruby-build.git ~/.rbenv/plugins/ruby-build
echo 'export PATH="$HOME/.rbenv/plugins/ruby-build/bin:$PATH"' >> ~/.bashrc
exec $SHELL

rbenv install 2.3.0 --verbose
rbenv global 2.3.0
rbenv rehash
gem install rubocop:0.30.0 rake

# Java
apt-get install default-jdk
apt-get install -y maven gradle

# Python
apt-get install python3
pip install tox virtualenv virtualenvwrapper
echo 'export WORKON_HOME=$HOME/.virtualenvs' >> ~/.bashrc
echo 'source /usr/local/bin/virtualenvwrapper.sh' >> ~/.bashrc

echo 'export PYTHONPATH="."' >> ~/.bashrc
exec $SHELL
#for PYVERSION in python2 python3
#do
#    mkvirtualenv ${PYVERSION} -p /usr/bin/${PYVERSION}
#    pip install pylint==1.4.5
#done
deactivate

# JavaScript
npm install -g eslint jshint jscs

echo 'export PATH="vendor/bin:$PATH"' >> ~/.bashrc
exec $SHELL
