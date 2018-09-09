Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.provider :virtualbox do |v|
    v.customize ["modifyvm", :id, "--memory", 2048]
  end
  config.vm.provision :shell, path: "vagrant/bootstrap.sh"
  config.vm.network "forwarded_port", guest: 80, host: 8087
  config.vm.network "forwarded_port", guest: 443, host: 8043
  config.vm.synced_folder ".", "/vagrant", type: "rsync",
     rsync__exclude: [".git/", "Vagrantfile"]
end