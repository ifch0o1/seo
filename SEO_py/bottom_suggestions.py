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
# import requests

base_keyword = str(sys.argv[1])
base_keyword = base_keyword.replace("_", " ")

print(base_keyword)
# exit()

# Server connection
if str(sys.argv).count('local') > 0:
    driver = webdriver.Chrome('/var/www/html/seo/SEO_py/chromedriver')  # Optional argument, if not specified will search path.
    apiUrl = 'http://79.124.39.68/api/push_python_words'
else:
    driver = webdriver.Remote(
        command_executor='http://127.0.0.1:4444/wd/hub', 
        desired_capabilities=DesiredCapabilities.CHROME)
    apiUrl = 'http://seo.maxprogress.bg/api/push_python_words'

driver.get('http://www.google.com/')

# Elements
search_box = driver.find_element_by_name('q')

time.sleep(0.7)
search_box.send_keys(base_keyword)
time.sleep(0.5)
search_box.send_keys(Keys.ENTER)

bottom_sugs = []
try:
    bottom_sugs_el = WebDriverWait(driver, 5).until(EC.presence_of_all_elements_located((By.CSS_SELECTOR, "#brs p > a")))
    for sug in bottom_sugs_el:
        bottom_sugs.append(sug.text)

    driver.quit()
except Exception as e:
    driver.quit()

print(json.dumps(bottom_sugs))