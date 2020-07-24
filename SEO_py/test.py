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

# GET FROM REQUEST
# symbols = "qwertyuiopasdfghjklzxcvbnm1234567890"
# Request parameters
# symbols = "абвгдежзийклмнопстуфхцчшщюя"



# Server connection
driver = webdriver.Chrome('/var/www/html/seo/SEO_py/chromedriver')  # Optional argument, if not specified will search path.

driver.get('http://www.google.com/')

try:
    login = WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.XPATH, "//*[contains(text(),'Sign in')]"))
        )
except:
    login = WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.XPATH, "//*[contains(text(),'Вход')]"))
        )

login.click()

email_input = WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "[type=email]"))
        )

time.sleep(3)

email_input.send_keys('ivo.h.tanev')
email_input.send_keys(Keys.ENTER)

time.sleep(4)

password_input = WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "[type=password]"))
        )

password_input.send_keys('string.split')
time.sleep(1)

password_input.send_keys(Keys.ENTER)

#seo.tracktor@gmail.com
#JSON.stringify(1)


time.sleep(20)

# driver.quit()
