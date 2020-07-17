#!/usr/bin/env python

import time
import sys
from selenium import webdriver
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait
import json
import requests

keyword = 'Уеб дизайн'
page_turns = 50
site = 'maxprogress.bg'

# if str(sys.argv).count('local') > 0:
driver = webdriver.Chrome('/var/www/html/seo/SEO_py/chromedriver')  # Optional argument, if not specified will search path.
    # apiUrl = 'http://79.124.39.68/api/push_python_words'
# else:
#     driver = webdriver.Remote(
#         command_executor='http://127.0.0.1:4444/wd/hub', 
#         desired_capabilities=DesiredCapabilities.CHROME)
#     apiUrl = 'http://seo.maxprogress.bg/api/push_python_words'

# server_ip = str(sys.argv[5])
# If localhost website API used - Change the apiUrl to save in local database.
# if (server_ip.count('192.168') > 0):
    # apiUrl = 'http://79.124.39.68/api/push_python_words'

driver.get('http://www.google.com/')
time.sleep(0.3)

search_box = driver.find_element_by_name('q')
search_box.send_keys(keyword)
search_box.submit()

def next_page():
    driver.find_element_by_xpath("//*[@id='pnnext']").click()
    time.sleep(0.3)

def previous_page():
    driver.find_element_by_xpath("//*[@id='pnprev']").click()
    time.sleep(0.3)

def get_current_page():
    current_page = driver.find_elements_by_xpath("//*[@id='foot']//td/span/..")[0].text
    return current_page

def get_results():
    links = driver.find_elements_by_xpath('//*[@id="search"]//*[@class="g"]//h3/../../a')
    results = []
    for link in links:
        href = link.get_attribute('href')
        print(href)
        results.append(href)
    
    return results

link_results = []

x = range(page_turns)
current_site_index = None
for n in x:
    link_list = get_results()
    link_results += link_list

    for index, l in enumerate(link_results):
        if l.count(site) > 0:
            current_site_index = index
            break
    
    if current_site_index:
        break
    next_page()

print(current_site_index + 1)
print(link_results)

driver.quit()