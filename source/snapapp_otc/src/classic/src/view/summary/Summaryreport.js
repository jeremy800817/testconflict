Ext.define("snap.view.summary.SummaryReport", {
    extend: 'Ext.panel.Panel',
    xtype: "summaryreport",
    cls: 'otc-main-left-dashboard-header',
    margin: '0 0 0 0',
    minHeight: 320,
    html: `
      <table style="border-collapse: collapse; margin-left: 10px; margin-top:10px;">
        <tr>
          <td style="border: none;"><b>Summary Report</b></td>
        </tr>
        <tr style="height: 20px;"></tr> <!-- Gap -->
        <tr>
          <td itemId="dateRange" style="border: none;"><b>From DD/MM/YYYY to DD/MM/YYYY</b></td>
        </tr>
        <tr style="height: 20px;"></tr> <!-- Gap -->
        <tr>
          <td style="border: 1px solid black;">Branch `+ PROJECTBASE + `</td>
          <td style="border: 1px solid black;">No. of Accounts</td>
          <td style="border: 1px solid black;">(g)</td>
          <td style="border: 1px solid black;">(RM)</td>
        </tr>
        <tr>
          <td style="border: 1px solid black;">Total Gold Purchased by Customer</td>
          <td style="border: 1px solid black;"></td>
          <td style="border: 1px solid black;"></td>
          <td style="border: 1px solid black;"></td>
        </tr>
        <tr>
          <td style="border: 1px solid black;">Total Gold Conversion</td>
          <td style="border: 1px solid black;"></td>
          <td style="border: 1px solid black;"></td>
          <td style="border: 1px solid black;"></td>
        </tr>
        <tr>
          <td style="border: 1px solid black;">Total Gold Sold by Customer</td>
          <td style="border: 1px solid black;"></td>
          <td style="border: 1px solid black;"></td>
          <td style="border: 1px solid black;"></td>
        </tr>
        <tr style="height: 20px;"></tr> <!-- Gap -->
        <tr>
        <td style="border: 1px solid black;">Branch` + PROJECTBASE + `</td>
          <td style="border: 1px solid black;">Individual Account</td>
          <td style="border: 1px solid black;">Joint Account</td>
          <td style="border: 1px solid black;">Company Account</td>
        </tr>
        <tr>
          <td style="border: 1px solid black;">No. of GII Accounts</td>
          <td style="border: 1px solid black;"></td>
          <td style="border: 1px solid black;"></td>
          <td style="border: 1px solid black;"></td>
        </tr>
        <tr style="height: 20px;"></tr> <!-- Gap -->
        <tr>
          <td style="border: 1px solid black;">Branch `+ PROJECTBASE + `</td>
          <td style="border: 1px solid black;">Acc with Balance</td>
          <td style="border: 1px solid black;">Acc with Nil Balance</td>
        </tr>
        <tr>
          <td style="border: 1px solid black;">No of GII Accounts</td>
          <td style="border: 1px solid black;"></td>
          <td style="border: 1px solid black;"></td>
        </tr>
      </table>
    `,
  });
  