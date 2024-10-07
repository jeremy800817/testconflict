Ext.define("snap.view.summary.SummaryController", {
    extend: "snap.view.gridpanel.BaseController",
    alias: "controller.Summary",

    generatesummaryhtml: function() {
        var startDate = this.lookupReference('startDate').getValue();
        var endDate = this.lookupReference('endDate').getValue();
        var branch = this.lookupReference('branchlist').getValue();

        if (!startDate || !endDate) {
            Ext.MessageBox.show({
                title: "Filter Date",
                msg: "Start date and End date are required.",
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR,
            });
            return;
        }

        if (startDate > endDate) {
            Ext.MessageBox.show({
                title: "Filter Date",
                msg: "Start date cannot be later than End date.",
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR,
            });
            return;
        }

        if (!branch) {
            Ext.MessageBox.show({
                title: "Filter Branch",
                msg: "Branch selection is required.",
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR,
            });
            return;
        }

        var me = this;

        snap.getApplication().sendRequest({
                hdl: 'summary',
                action: 'getSummaryData',
                startDate: Ext.Date.format(startDate, 'Y-m-d'),
                endDate: Ext.Date.format(endDate, 'Y-m-d'),
                branch: branch
            }, 'Fetching data from server....')
            .then(function(data) {
                console.log(data);
                if (data.success) {
                    var totalAccountsgoldpurchased = data.data.totalAccountsgoldpurchased;
                    var totalAccountsgoldconvert = data.data.totalAccountsgoldconvert;
                    var totalAccountsgoldsold = data.data.totalAccountsgoldsold;
                    var totalGramspurchased = data.data.totalGramspurchased;
                    var totalGramsconvert = data.data.totalGramsconvert;
                    var totalGramssold = data.data.totalGramssold;
                    var totalRMpurchased = data.data.totalRMpurchased;
                    var totalRMConvert = data.data.totalRMConvert;
                    var totalRMsold = data.data.totalRMsold;
                    var IndividualAcc = data.data.IndividualAcc;
                    var JointAcc = data.data.JointAcc;
                    var CompanyAcc = data.data.CompanyAcc;
                    var AccBalance = data.data.AccBalance;
                    var AccNilBalance = data.data.AccNilBalance;

                    me.getView().updateSummaryReport(startDate, endDate, branch, totalAccountsgoldpurchased, totalAccountsgoldconvert, totalAccountsgoldsold, 
                        totalGramspurchased, totalGramsconvert, totalGramssold, totalRMpurchased, totalRMConvert, totalRMsold,
                        IndividualAcc, JointAcc, CompanyAcc, AccBalance, AccNilBalance);
                } else {
                    // Handle error response
                    Ext.MessageBox.show({
                        title: "Summary Data",
                        msg: "Failed to retrieve summary data.",
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR,
                    });
                }
            })
            .catch(function(error) {
                Ext.MessageBox.show({
                    title: "Summary Data",
                    msg: "Failed to retrieve summary data.",
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR,
                });
            });
    },




    clearsummaryhtml: function() {
        var startDate = this.lookupReference('startDate').getValue();
        var endDate = this.lookupReference('endDate').getValue();
        var branch = this.lookupReference('branchlist').getValue();


        if (!startDate && !endDate && !branch) {
            Ext.MessageBox.show({
                title: "Clear Filter",
                msg: "No filter applied.",
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR,
            });
            return;
        } else {
            this.lookupReference('startDate').reset();
            this.lookupReference('endDate').reset();
            this.lookupReference('branchlist').reset();
        }

        this.getView().clearSummaryReport();
    },

    PrintReportHTML: function() {
        var summaryReport = this.getView().down('#summaryReport');
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Summary Report</title></head><body>');
        // printWindow.document.write('<h1>Summary Report</h1>');
        printWindow.document.write(summaryReport.getEl().dom.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
        printWindow.focus();
    }
});