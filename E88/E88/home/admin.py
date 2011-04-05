# coding=utf-8

from home.models import *
from django.contrib import admin


class CountryAdmin(admin.ModelAdmin):
    list_display = ('name', 'printable_name', 'iso', 'numcode', )

class ProvinceAdmin(admin.ModelAdmin):
    list_display = ('name', 'country', )

class DomainAdmin(admin.ModelAdmin):
    fields = ('domain','enabled')
    list_display = ('domain', 'enabled', )





admin.site.register(Country, CountryAdmin)
admin.site.register(Province, ProvinceAdmin)
admin.site.register(Domain, DomainAdmin)

admin.site.register(Channel)
admin.site.register(Announcement)
