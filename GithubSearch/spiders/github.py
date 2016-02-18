# -*- coding: utf-8 -*-
import scrapy


class GithubSpider(scrapy.Spider):
    name = "github"
    allowed_domains = ["github.com"]
    start_urls = ['https://github.com/search?utf8=%E2%9C%93&q=pylintrc+in%3Apath+path%3A%2F&type=Code&ref=searchresults']

    def parse(self, response):
        for href in response.css('.code-list-item p.title a:first-child::attr(href)'):
            # url = response.urljoin(href.extract())
            # yield scrapy.Request(url, callback=self.parse_repository)
            yield {'repository': href.extract()}
        next_page = response.css('.pagination a.next_page::attr(href)')
        if next_page:
            url = response.urljoin(next_page[0].extract())
            yield scrapy.Request(url, self.parse)

    def parse_repository(self, response):
         yield {
            'url': response.url,
            'description': response.css('.repository-meta-content').xpath('span[@itemprop="about"]/text()').extract()
         }
