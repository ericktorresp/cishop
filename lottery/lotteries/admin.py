from mysite.lotteries.models import Lottery, LotteryTypefrom django.contrib import adminclass LotteryAdmin(admin.ModelAdmin):    list_display = ('title', 'code')#    list_filter = ['created_at']    search_fields = ['title']##class LotteryTypeAdmin(admin.ModelAdmin):#    list_display = ('title')#    search_fields = ['title']    admin.site.register(Lottery, LotteryAdmin)admin.site.register(LotteryType)