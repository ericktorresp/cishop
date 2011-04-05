# coding=utf-8

from bank.models import *
from django.contrib import admin

from home.widgets import ImageWidget

class BankAdmin(admin.ModelAdmin):
    def formfield_for_dbfield(self, db_field, **kwargs):
        if db_field.name == 'logo':
            request = kwargs.pop("request", None)
            kwargs['widget'] = ImageWidget
            return db_field.formfield(**kwargs)
        return super(BankAdmin,self).formfield_for_dbfield(db_field, **kwargs)
    
    list_display = ('name', 'code', 'logo', )



class PaymentMethodAdmin(admin.ModelAdmin):
    def formfield_for_dbfield(self, db_field, **kwargs):
        if db_field.name == 'logo':
            request = kwargs.pop("request", None)
            kwargs['widget'] = ImageWidget
            return db_field.formfield(**kwargs)
        return super(PaymentMethodAdmin, self).formfield_for_dbfield(db_field, **kwargs)
    
admin.site.register(Bank, BankAdmin)
admin.site.register(Card)
admin.site.register(PayMethod, PaymentMethodAdmin)