from netaddr import IPNetwork

for ip in IPNetwork('172.20.0.1/16'):
    print("%s \t free" %ip)