#!/usr/bin/env python3

#
# Copyright (C) 2019  ahahn94
#     This program is free software: you can redistribute it and/or modify
#     it under the terms of the GNU General Public License as published by
#     the Free Software Foundation, either version 2 of the License, or
#     (at your option) any later version.
#
#     This program is distributed in the hope that it will be useful,
#     but WITHOUT ANY WARRANTY; without even the implied warranty of
#     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#     GNU General Public License for more details.
#
#     You should have received a copy of the GNU General Public License
#     along with this program.  If not, see <https://www.gnu.org/licenses/>.
#

#
# manage.py is the script that controls the ComicLib servers containers.
# Aside from starting and stopping the containers, it will also make sure that all necessary files
# are writable by the web servers user 'www-data'.
#

import configparser
import platform
import subprocess
from os import environ
from os import system

from sys import argv

# Files that the server needs write access to.
files_with_write_access = ["src/cache", "src/log.txt", "src/updater.lock"]


def start():
    """
    Start the containers of the ComicLib server.
    :return: None
    """
    print("Starting server...")
    # Check if server has write access to cache, log and lock files. Fix if not.
    if not check_permissions():
        fix_permissions()
    docker_control("start")


def stop():
    """
    Stop the containers of the ComicLib server.
    :return: None
    """
    print("Stopping server...")
    docker_control("stop")


def fix_permissions():
    """
    This will assure that the user www (user id 33 on the container) has write access to the cache, log and lock files.
    :return: None
    """
    print("Fixing permissions on the web root...")
    system("sudo chown -R 33 " + " ".join(files_with_write_access))


def clear_log():
    """
    Delete all content from the log file.
    :return: None
    """
    print("Clearing the log file...")
    system("sudo truncate -s 0 src/log.txt; sudo chown 33 src/log.txt")


def help_screen():
    """
    Print the help screen.
    :return: None
    """
    print("Welcome to the ComicLib server manager.")
    print("Use 'manage.py start' to start the containers of the ComicLib server.")
    print("Use 'manage.py stop' to stop the containers of the ComicLib server.")
    print("Use 'manage.py fix-permissions' to grant writing permission on the necessary files to the web server.")
    print("Use 'manage.py check-permissions' to check writing permission on the necessary files.")
    print("Use 'manage.py clear-log' to to clear the log file src/log.txt.")


def prepare_config():
    """
    Read the config from the config.ini file.
    Create environment variables from the config.
    This will be used by docker compose to init the containers with the parameters
    from config.ini.
    :return: None
    """
    config = configparser.ConfigParser()
    # Preserve case of keys and values
    config.optionxform = str
    config.read("config.ini")
    for section in config.sections():
        for (key, value) in config.items(section):
            environ[key] = value


def check_permissions():
    """
    This will check if the user www (user id 33 on the container) has write access to the cache, log and lock files.
    :return: True if ok, else False
    """
    write_access = True
    for file in files_with_write_access:
        # Check for all files from the list, if the user id is '33' (= www-data).
        uid = subprocess.getoutput("stat -c '%u' " + file)
        if uid != "33":
            write_access = False
    return write_access


def determine_arch():
    """
    Determine the architecture (x86 or arm) of the host.
    :return: x86 or arm
    """
    arch = getattr(platform.uname(), "machine")
    if "arm" in arch:
        return "arm"
    else:
        return "x86"


def docker_control(command):
    """
    Control the docker containers.
    :param command: Either start or stop to start or stop the containers.
    :return: None
    """
    prepare_config()
    # Choose the compose file based on processor architecture.
    arch = determine_arch()
    if arch == "arm":
        compose_file = "armhf.yaml"
    else:
        compose_file = "amd64.yaml"
    # Run the specified command.
    if command == "start":
        system("docker-compose -f " + compose_file + " up -d")
    elif command == "stop":
        system("docker-compose -f " + compose_file + " stop")


#
# Program start point.
#

# Read startup parameter
params = argv[1:]

# If no parameter, start server.
if len(params) == 0:
    start()
# Else check which parameter.
else:
    if params[0] == "start":
        start()
    elif params[0] == "stop":
        stop()
    elif params[0] == "fix-permissions":
        fix_permissions()
    elif params[0] == "check-permissions":
        print(check_permissions())
    elif params[0] == "clear-log":
        clear_log()
    else:
        help_screen()
