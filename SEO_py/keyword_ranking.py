#!/usr/bin/env python

import time
import sys
from selenium import webdriver
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait
# from selenium.common.exceptions import TimeoutException
import random
import json
import requests
from urllib.parse import urlparse
from datetime import datetime
from selenium.webdriver.common.action_chains import ActionChains
import re



def get_domain(url):
    return urlparse(url)

cron = str(sys.argv).count('cron') > 0

if cron:
    apiUrl = 'https://seo.maxprogress.bg/api/keyword-ranking-words'
    driver = webdriver.Remote(
        command_executor='http://127.0.0.1:4444/wd/hub',
        desired_capabilities=DesiredCapabilities.FIREFOX)

    # Set sleeps
    minSleep = 40
    maxSleep = 120
else:
    # apiUrl = 'http://79.124.36.172/api/keyword-ranking-words'
    apiUrl = 'https://seo.maxprogress.bg/api/keyword-ranking-words'
    driver = webdriver.Chrome('/var/www/html/seo/SEO_py/chromedriver')  # Optional argument, if not specified will search path.
    # driver = webdriver.Firefox('/var/www/html/seo/SEO_py/')  # Optional argument, if not specified will search path.

    # Set sleeps
    minSleep = 10
    maxSleep = 15

page_turns = 2

def random_sleep():
    time.sleep(random.randint(minSleep, maxSleep))

driver.maximize_window()

driver.get('http://www.google.com/')
random_sleep()
    
def find_position(keyword, siteHref):
    site = get_domain(siteHref).netloc
    if site == '':
        site = siteHref

    if site == '':
        return False

    search_box = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.NAME, "q"))
    )

    # Sometimes this search filed changes.
    # (may be google change it for security reasons)
    try:
        search_box.clear()
        time.sleep(1)
        search_box.send_keys(keyword)
        time.sleep(1.5)
        search_box.submit()
    except Exception as ee:
        print(str(ee))
        time.sleep(10)
        driver.get('http://www.google.com/')
        time.sleep(20)
        print('entering recursion - search field raise an exception: Sleep 10 - enter google.com - sleep 20 sec and search again')
        return find_position(keyword, siteHref)

    link_results = [] # not used for now

    x = range(page_turns)
    current_site_index = False
    for n in x:
        time.sleep(2)
        print('searching page', n + 1)
        link_list = get_results()
        # print('list: ', link_list)
        link_results += link_list

        ad_found_at = None
        organic_found_at = None
        
        for index, result in enumerate(link_results):
            if result['href'] == None:
                continue
                
            linkLower = result['href'].lower()
            siteLower = site.lower()

            if linkLower.count(siteLower) > 0:
                if (result['ad']):
                    ad_found_at = ad_found_at if ad_found_at else index
                    print('[AD] found at position: ', ad_found_at)
                else:
                    organic_found_at = organic_found_at if organic_found_at else index
                    print('[ORGANIC] found at position: ', organic_found_at)

        # Both are found. So we break
        if organic_found_at:
            break
        
        else:
            # Clicking next_page() - if next page return false - no next page button exists (no more pages available)
            if n < page_turns:
                if next_page() == False:
                    return False

    if ad_found_at or organic_found_at:
        # Adding results ot dict to process either later
        found_results = {'ad': None, 'organic': None}
        
        if ad_found_at:
            found_results['ad'] = link_results[ad_found_at]
            found_results['ad']['position'] = ad_found_at
            
        if organic_found_at:
            found_results['organic'] = link_results[organic_found_at]
            found_results['organic']['position'] = organic_found_at
        
        return {'results': found_results}
    else:
        return False

def next_page():
    try:
        next_page_link = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.XPATH, "//*[@id='pnnext']"))
        )

        random_sleep()
        next_page_link.click()
        random_sleep()
        
        return True
    
    except Exception as e:
        print('next page error')
        print(str(e))
        return False

def previous_page():
    try:
        prev_page_link = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.XPATH, "//*[@id='pnprev']"))
        )

        random_sleep()
        prev_page_link.click()
        random_sleep()
        return True
    
    except Exception as e:
        print('prev page error')
        print(str(e))
        return False

def get_current_page():
    try:
        current_page = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.XPATH, "//*[@id='foot']//td/span/.."))
        )
        return current_page.text
    except:
        print('current page error')
        return False
    
def check_if_link_is_ad(link):
    # Checking the links for ad text.
    ad = len(link.find_elements(By.XPATH, ".//span[contains(text(),'Реклама')]"))
    if ad == 0:
        ad = len(link.find_elements(By.XPATH, ".//span[contains(text(),'Ad')]"))
        
    # Normalize ad
    if ad > 1:
        ad = 1
        
    return ad
    

def filter_links_to_extract_only_google_results(links):
    filtered = []
    for link in links:
        linkText = link.text
        href = link.get_attribute('href')
        
        if href == None:
            continue
        
        notGoogleLink = (href.count('google') == 0 and href.count('webcache') == 0)
        
        if href and notGoogleLink:
            ad = check_if_link_is_ad(link)
                
            try:
                filtered.append({
                    'ad': ad,
                    'href': href,
                    'title': linkText
                })
            except Exception as e:
                print(str(e))
    return filtered


def get_results():
    results_xpath = '//a'

    try:
        links = WebDriverWait(driver, 10).until(
            EC.presence_of_all_elements_located((By.XPATH, results_xpath))
        )
    except:
        # Wait ~60-80 minutes until google removed a captcha.
        # time.sleep(4500)
        # driver.get('http://www.google.com/')
        print("May be captcha hitted.")
        return []

    return filter_links_to_extract_only_google_results(links)

def store_ranking_data(data):
    return requests.post(apiUrl, json={
        'keyword_id': data['keyword_id'],
        'client_id': data['client_id'],
        'position': data['position'],
        'link': data['link'],
        'title': data['title'],
        'ad': data['ad']
    })

r = requests.get(apiUrl)

data = json.loads(r.text)

for href_data in data:
    print('starting for site: ', href_data['site'])
    print('searching for: ' , href_data['keyword'])
    
    # keyword with ads
    # if href_data['keyword'] != 'цени счетоводни услуги Русе':
    #     continue

    posData = find_position(href_data['keyword'], href_data['site'])
    print(posData)
    
    if posData:
        results = posData['results']
        # print('results', results)
        
        if 'ad' in results and results['ad']:
            res = store_ranking_data({
                'keyword_id': href_data['keyword_id'],
                'client_id': href_data['client_id'],
                'position': results['ad']['position'] if results['ad']['position'] else '0',
                'link': results['ad']['href'] if results['ad']['href'] else '--',
                'title': results['ad']['title'] if results['ad']['title'] else '--',
                'ad': 1
            })
            if res:
                print('Saving ad result: ', res.status_code)
            
        if 'organic' in results and results['organic']:
            res = store_ranking_data({
                'keyword_id': href_data['keyword_id'],
                'client_id': href_data['client_id'],
                'position': results['organic']['position'] if results['organic']['position'] else '0',
                'link': results['organic']['href'] if results['organic']['href'] else '--',
                'title': results['organic']['title'] if results['organic']['title'] else '--',
                'ad': 0
            })
            if res:
                print('Saving organic result: ', res.status_code)
            
    else:
        res = store_ranking_data({
            'keyword_id': href_data['keyword_id'],
            'client_id': href_data['client_id'],
            'position': 0,
            'ad': 0,
            'link': '--',
            'title': '--'
        })
        if res:
            print(res.status_code)
        
        res = store_ranking_data({
            'keyword_id': href_data['keyword_id'],
            'client_id': href_data['client_id'],
            'position': 0,
            'ad': 1,
            'link': '--',
            'title': '--'
        })
        if res:
            print(res.status_code)

    print("====================================")

driver.quit()