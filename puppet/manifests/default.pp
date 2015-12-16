# define paths
Exec {path => ['/bin/', '/sbin/', '/usr/bin/', '/usr/sbin/']}

# set global packages params
Package {ensure => installed}

# restart apache
class restart_apache {
  exec {'service apache2 restart':
    require => Class['install_apache']
  }
}

# initialize - install prerequisites
class initialize {
  package {'python_software_properties':
    name => 'python-software-properties'
  }

  exec {'apt_get_update':
    command => 'apt-get update',
    require => Package['python_software_properties']
  }

  exec {'add_apt_repo':
    command => 'add-apt-repository -y ppa:ondrej/php5-5.6',
    require => Exec['apt_get_update']
  }

  exec {'apt_get_upgrade':
    command => 'apt-get update && apt-get --quiet --yes --fix-broken upgrade',
    require => Exec['add_apt_repo']
  }
}

# install and run apache
class install_apache {
  package {'apache':
    name => 'apache2',
    require => Class['initialize']
  }

  service {'apache':
    name => 'apache2',
    ensure => running,
    require => Package['apache']
  }
}

# set symling to project folder
class set_symlink {
  exec {'clear_www_folder':
    command => 'rm -rf /var/www/html',
    require => Class['install_apache']
  }

  file {'link':
    path => '/var/www/html',
    ensure => link,
    target => '/vagrant',
    require => Exec['clear_www_folder']
  }
}

# install and configure php
class install_php {
  package {'php':
    name => 'php5',
    require => Class['install_apache']
  }

  package {'libapache':
    name => 'libapache2-mod-php5',
    require => Package['php']
  }

  package {'mcrypt':
    name => 'php5-mcrypt',
    require => Package['libapache']
  }

  package {'sqlite':
    name => 'php5-sqlite',
    require => Package['mcrypt']
  }

  exec {'apache_enable_php':
    command => 'a2enmod php5',
    require => Package['sqlite']
  }

  file {'configure_php':
    path => '/etc/php5/apache2/conf.d/my_php_settings.ini',
    ensure => present,
    owner => root,
    group => root,
    mode => 444,
    content => "display_errors = On\nextension=sqlite3.so\n",
    require => Exec['apache_enable_php'],
    before => Class['restart_apache']
  }
}

include initialize
include install_apache
include install_php
include set_symlink
include restart_apache
