import React, { forwardRef, Fragment, useEffect, useImperativeHandle, useRef, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { fetchDepartmentFilterData, updateSelectedDepartments } from '../../../store/actions/common/department-filter';
import MultiSelectTreeCheckboxDropdown from '../../../common/multi-select-tree-checkbox-dropdown/MultiSelectTreeCheckboxDropdown';

function DepartmentFilterWithOutDataScope(props, ref) {
    const MultiSelectTreeCheckboxDropdownRef = useRef(null)
    /* */
    const dispatch = useDispatch()
    const [departmentTreeData, setDepartmentTreeData] = useState([]);
    // const appDataScope = useSelector(state => state.appDataScope.selectedDataScope.value)
    // const { departmentTreeData } = useSelector(state => state.commonReducer.departmentFilterReducer)

    useEffect(() => {
        getAllDepartments();
    }, [])

    /* re-fetching department filter data on app data scope update*/
    // useEffect(() => {
    //     dispatch(fetchDepartmentFilterData({
    //         data_scope: appDataScope
    //     }))
    // }, [appDataScope])

    // The component instance will be extended
    // with whatever you return from the callback passed
    // as the second argument
    // useImperativeHandle(ref, () => ({
    //     selectAll,
    // }));

    /* Handling department checked update */
    const handleDepartmentSelect = (selectedDepartments) => {
        dispatch(updateSelectedDepartments(selectedDepartments))
    }

    /* Get All Departments */
    const getAllDepartments = () => {
        axiosFetch.get(route('common.get-all-department-filter-tree-view-data')).then(res => {
            let response = res.data
            if (response.success) {
                let data = response.data
                setDepartmentTreeData(data)
            }
        }).catch((error) => { console.log(error) })
    }

    /* renderable data update callback */
    const handleRenderableDataUpdate = ({ dataUpdateFromParent }) => {
        if (dataUpdateFromParent) {
            selectAll()
        }
    }

    /* select all*/
    const selectAll = () => {
        MultiSelectTreeCheckboxDropdownRef.current.selectAll()
    }

    return (
        <Fragment>
            <MultiSelectTreeCheckboxDropdown
                ref={MultiSelectTreeCheckboxDropdownRef}
                treeData={departmentTreeData}
                onCheck={handleDepartmentSelect}
                width="190"
                renderableDataUpdate={handleRenderableDataUpdate}
            >
            </MultiSelectTreeCheckboxDropdown>
        </Fragment>
    );
}

export default forwardRef(DepartmentFilterWithOutDataScope);
