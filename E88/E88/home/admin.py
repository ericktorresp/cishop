# coding=utf-8

from home.models import *
from django.contrib import admin

class CountryAdmin(admin.ModelAdmin):
    list_display = ('name', 'printable_name', 'iso', 'numcode', )

class ProvinceAdmin(admin.ModelAdmin):
    list_display = ('name', 'country', )



admin.site.register(Country, CountryAdmin)
admin.site.register(Province, ProvinceAdmin)
admin.site.register(Domain)
admin.site.register(Bank)
admin.site.register(Card)
admin.site.register(Channel)
admin.site.register(Announcement)