Ext.define("snap.view.summary.Summary_alrajhi", {
    extend: 'Ext.panel.Panel',
    xtype: "summaryalrajhi",
    controller: 'Summary',
    cls: Ext.baseCSSPrefix + 'shadow',
    viewModel: 'summary-summary',
    layout: {
      type: 'vbox',
      pack: 'start',
      align: 'stretch'
    },
    scrollable: true,
    bodyPadding: 10,
    defaults: {
      frame: true,
    },
    cls: 'otc-main',
    bodyCls: 'otc-main-body',
    items: [
      {
        xtype: "toolbar",
        items: [
            {
                xtype: "datefield",
                fieldLabel: "Start Date",
                name: "start_date",
                reference: "startDate",
                format: "d/m/Y",
            },
            {
                xtype: "datefield",
                fieldLabel: "End Date",
                name: "end_date",
                reference: "endDate",
                format: "d/m/Y",
            },
            {
                xtype: 'combobox',
                fieldLabel: 'Branch',
                reference: 'branchlist',
                allowBlank: true,
                editable: true,
                name: 'partnerid',
                store: {
                    autoLoad: true,
                    type: 'Partner',
                    sorters: 'name'
                },
                listConfig: {
                    getInnerTpl: function () {
                        return '[ {code} ] {name}';
                    }
                },
                displayTpl: Ext.create('Ext.XTemplate',
                    '<tpl for=".">',
                    '[ {code} ] {name}',
                    '</tpl>'
                ),
                displayField: 'name',
                valueField: 'code',
                typeAhead: true,
                queryMode: 'local',
                forceSelection: true,
                listeners: {
                    expand: function(combo){
                        combo.store.load({
                            start: 0,
                            limit: 1500
                        })
                    }
                },
            },
            {
            iconCls: 'x-fa fa-redo-alt',
            xtype: "button",
            text: "Generate",
            handler: "generatesummaryhtml",
            },
            {
            iconCls: 'x-fa fa-times-circle',
            xtype: "button",
            text: "Clear",
            handler: "clearsummaryhtml",
            },
            {
            iconCls: 'x-fa fa-file',
            xtype: "button",
            text: "Print",
            handler: "PrintReportHTML",
            
            },
        ],
      },
      {
        cls: 'otc-main-left-dashboard-header',
        margin: '10 0 0 0',
        minHeight: 350,
        itemId: 'summaryReport', // add itemId to easily access the element
      },
    ],
  
    // Initialize the HTML content
    initComponent: function() {
      this.callParent(arguments);
      this.updateSummaryReport ('', '', '', '', '', '', '', '', '', '', '', '','', '', '', '', ''); // Initial empty values
    },
  
    // Add a method to update the summary report HTML
    updateSummaryReport: function 
    (startDate, endDate, branch, totalAccountsgoldpurchased, totalAccountsgoldconvert, totalAccountsgoldsold, 
        totalGramspurchased, totalGramsconvert, totalGramssold, totalRMpurchased, totalRMConvert, totalRMsold,
        IndividualAcc, JointAcc, CompanyAcc, AccBalance, AccNilBalance) {
      var summaryReport = this.down('#summaryReport');
  
      var formattedStartDate = startDate ? Ext.Date.format(startDate, 'd/m/Y') : 'DD/MM/YYYY';
      var formattedEndDate = endDate ? Ext.Date.format(endDate, 'd/m/Y') : 'DD/MM/YYYY';
      var formattedBranch = branch ? branch :'XXX';
  
      var html = `
        <table style="border-collapse: collapse; margin-left: 20px; margin-top:10px;">
            <tr>
                <td style="border: none;"><b><span style="font-size: 25px;">Summary Report</span></b></td>
            </tr>
            <tr style="height: 20px;"></tr> <!-- Gap -->
            <tr>
                <td itemId="dateRange" style="border: none;font-size: 20px;"><b>From ${formattedStartDate} to ${formattedEndDate}</b></td>
            </tr>
            <tr style="height: 20px;"></tr> <!-- Gap -->
            <tr>
                <td style="border: 1px solid black;font-size: 16px;"><b>Branch ${formattedBranch}</b></td>
                <td style="border: 1px solid black;font-size: 16px;"><b>No. of Accounts</b></td>
                <td style="border: 1px solid black;font-size: 16px;"><b>(g)</b></td>
                <td style="border: 1px solid black;font-size: 16px;"><b>(RM)</b></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;font-size: 16px;"><b>Total Gold Purchased by Customer</b></td>
                <td style="border: 1px solid black;font-size: 16px;">${totalAccountsgoldpurchased}</td>
                <td style="border: 1px solid black;font-size: 16px;">${totalGramspurchased}</td>
                <td style="border: 1px solid black;font-size: 16px;">${totalRMpurchased}</td>
            </tr>
            <tr>
                <td style="border: 1px solid black;font-size: 16px;"><b>Total Gold Conversion</b></td>
                <td style="border: 1px solid black;font-size: 16px;">${totalAccountsgoldconvert}</td>
                <td style="border: 1px solid black;font-size: 16px;">${totalGramsconvert}</td>
                <td style="border: 1px solid black;font-size: 16px;">${totalRMConvert}</td>
            </tr>
            <tr>
                <td style="border: 1px solid black;font-size: 16px;"><b>Total Gold Sold by Customer</b></td>
                <td style="border: 1px solid black;font-size: 16px;">${totalAccountsgoldsold}</td>
                <td style="border: 1px solid black;font-size: 16px;">${totalGramssold}</td>
                <td style="border: 1px solid black;font-size: 16px;">${totalRMsold}</td>
            </tr>
            <tr style="height: 20px;"></tr> <!-- Gap -->
            <tr>
                <td style="border: 1px solid black;font-size: 16px;"><b>Branch ${formattedBranch}</b></td>
                <td style="border: 1px solid black;font-size: 16px;"><b>Individual Account</b></td>
                <td style="border: 1px solid black;font-size: 16px;"><b>Joint Account</b></td>
                <td style="border: 1px solid black;font-size: 16px;"><b>Company Account</b></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;font-size: 16px;"><b>No. of GII Accounts</b></td>
                <td style="border: 1px solid black;font-size: 16px;">${IndividualAcc}</td>
                <td style="border: 1px solid black;font-size: 16px;">${JointAcc}</td>
                <td style="border: 1px solid black;font-size: 16px;">${CompanyAcc}</td>
            </tr>
            <tr style="height: 20px;"></tr> <!-- Gap -->
            <tr>
                <td style="border: 1px solid black;font-size: 16px;"><b>Branch ${formattedBranch}</b></td>
                <td style="border: 1px solid black;font-size: 16px;"><b>Acc with Balance</b></td>
                <td style="border: 1px solid black;font-size: 16px;"><b>Acc with Nil Balance</b></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;font-size: 16px;"><b>No of GII Accounts</b></td>
                <td style="border: 1px solid black;font-size: 16px;">${AccBalance}</td>
                <td style="border: 1px solid black;font-size: 16px;">${AccNilBalance}</td>
            </tr>
        </table>`;
        summaryReport.setHtml(html);
    },
  
    // Function to clear the summary report
    clearSummaryReport: function () {
      this.updateSummaryReport ('', '', '', '', '', '', '', '', '', '', '', '','', '', '', '', ''); // Clear values
    }
  });
  