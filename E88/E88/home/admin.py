# coding=utf-8

from home.models import *
from account.models import *
from django.contrib import admin
from django.contrib.auth.admin import UserAdmin

from home.widgets import ImageWidget

class CountryAdmin(admin.ModelAdmin):
    list_display = ('name', 'printable_name', 'iso', 'numcode', )

class ProvinceAdmin(admin.ModelAdmin):
    list_display = ('name', 'country', )

class DomainAdmin(admin.ModelAdmin):
    fields = ('domain','enabled')
    list_display = ('domain', 'enabled', )

class BankAdmin(admin.ModelAdmin):
    def formfield_for_dbfield(self, db_field, **kwargs):
        if db_field.name == 'logo':
            request = kwargs.pop("request", None)
            kwargs['widget'] = ImageWidget
            return db_field.formfield(**kwargs)
        return super(BankAdmin,self).formfield_for_dbfield(db_field, **kwargs)
    
    list_display = ('name', 'code', 'logo', )

class UserProfileInline(admin.StackedInline):
    model = UserProfile
    can_delete = False
class UserCardInline(admin.StackedInline):
    model = UserCard
    extra = 3


class ProfileAdmin(UserAdmin):
    inlines = (
        UserProfileInline,
        UserCardInline,
    )

admin.site.unregister(User)
admin.site.register(User, ProfileAdmin)
admin.site.register(Country, CountryAdmin)
admin.site.register(Province, ProvinceAdmin)
admin.site.register(Domain, DomainAdmin)
admin.site.register(Bank, BankAdmin)
admin.site.register(Card)
admin.site.register(Channel)
admin.site.register(Announcement)
admin.site.register(PayMethod)
